<?php

declare(strict_types=1);

namespace Mautic\FormBundle\Tests\Controller;

use Mautic\CoreBundle\Test\MauticMysqlTestCase;
use Mautic\FormBundle\Entity\Field;
use Mautic\FormBundle\Entity\Form;
use Mautic\FormBundle\Model\FormModel;
use Mautic\LeadBundle\Entity\Company;
use Mautic\LeadBundle\Tracker\ContactTracker;
use Mautic\PageBundle\Entity\Page;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PublicControllerFunctionalTest extends MauticMysqlTestCase
{
    public function testFormPreviewWithToken(): void
    {
        $page = (new Page())
            ->setTitle('Test Page')
            ->setAlias('this-is-a-slug');
        $this->em->persist($page);
        $this->em->flush();

        $form = new Form();
        $form->setName('Token Test')
            ->setAlias('token_test')
            ->setFormType('standalone')
            ->setPostAction('message')
            ->setPostActionProperty('Thanks!')
            ->setIsPublished(true);
        // ->setCachedHtml('');
        $form->setRenderStyle(0);

        $descriptionArea = (new Field())
            ->setForm($form)
            ->setLabel('Description Area')
            ->setAlias('description_area')
            ->setType('freetext')
            ->setProperties([
                'text' => '<p>{today|date}</p><p>{pagelink='.$page->getId().'}</p><p>{contactfield=id}</p><p data-lead="email">{contactfield=email}</p>',
            ]);

        $this->em->persist($descriptionArea);

        $form->addField(1, $descriptionArea);

        $formModel = static::getContainer()->get(FormModel::class);
        assert($formModel instanceof FormModel);
        $formModel->saveEntity($form);

        $formHtml = $form->getCachedHtml();
        // dump($formHtml);

        $this->assertStringContainsString(
            '{pagelink='.$page->getId().'}',
            $formHtml,
            'Pagelink tokens should be present in the database for later substitution.'
        );
        $this->assertStringContainsString(
            '{today|date}',
            $formHtml,
            'Date tokens should be present in the database for later substitution.'
        );
        $this->assertStringContainsString(
            '{contactfield=id}',
            $formHtml,
            'Contactfield tokens should be present in the database for later substitution.'
        );

        $crawler = $this->client->request(Request::METHOD_GET, "/form/{$form->getId()}", []);

        $formPreviewHtml = $crawler->html();

        $contactTracker = static::getContainer()->get(ContactTracker::class);
        assert($contactTracker instanceof ContactTracker);

        $lead = $contactTracker->getContact();

        $this->assertStringContainsString(
            '<p>https://localhost/this-is-a-slug</p>',
            $formPreviewHtml,
            'Pagelink tokens should be substitutet with URL.'
        );
        $this->assertStringContainsString(
            '<p>'.(new \DateTime())->format('F j, Y').'</p>',
            $formPreviewHtml,
            'Date tokens should substituted with config date format.'
        );
        $this->assertStringContainsString(
            '<p>'.$lead->getId().'</p>',
            $formPreviewHtml,
            'Contactfield tokens should be substituted with lead values.'
        );
        $this->assertStringContainsString(
            '<p data-lead="email"></p>',
            $formPreviewHtml,
            'Contactfield tokens without value should be substituted with empty string.'
        );
    }

    public function testLookupActionWithNoLookupFormField(): void
    {
        $this->makeRequest(['string' => 'Company']);
        $clientResponse = $this->client->getResponse();

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $clientResponse->getStatusCode(), $clientResponse->getContent());
        Assert::assertSame('{"error":"Invalid request param"}', $clientResponse->getContent(), $clientResponse->getContent());
    }

    public function testLookupActionWithInvalidLookupFormField(): void
    {
        $this->makeRequest(['string' => 'Company', 'formId' => 3]);
        $clientResponse = $this->client->getResponse();

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $clientResponse->getStatusCode(), $clientResponse->getContent());
        Assert::assertSame('{"error":"Invalid request param"}', $clientResponse->getContent(), $clientResponse->getContent());
    }

    public function testLookupActionWithTooFewLetters(): void
    {
        $form = $this->createForm();

        $this->makeRequest(['string' => 'Co', 'formId' => $form->getId()]);
        $clientResponse = $this->client->getResponse();

        Assert::assertSame(Response::HTTP_BAD_REQUEST, $clientResponse->getStatusCode(), $clientResponse->getContent());
        Assert::assertSame('{"error":"Invalid request param"}', $clientResponse->getContent(), $clientResponse->getContent());
    }

    public function testLookupActionWithCompanyData(): void
    {
        $this->createCompany('Unicorn A');
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B', 'Boston', 'Massachusetts');
        $form     = $this->createForm();

        $this->makeRequest(['search' => 'Company', 'formId' => $form->getId()]);
        $clientResponse = $this->client->getResponse();

        Assert::assertTrue($clientResponse->isOk(), $clientResponse->getContent());
        Assert::assertSame(
            [
                [
                    'id'           => (string) $companyA->getId(),
                    'companyname'  => 'Company A',
                    'companycity'  => null,
                    'companystate' => null,
                ],
                [
                    'id'           => (string) $companyB->getId(),
                    'companyname'  => 'Company B',
                    'companycity'  => 'Boston',
                    'companystate' => 'Massachusetts',
                ],
            ],
            json_decode($clientResponse->getContent(), true)
        );
    }

    /**
     * @param mixed[] $payload
     */
    private function makeRequest(array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/form/company-lookup/autocomplete',
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode($payload)
        );
    }

    private function createCompany(string $name, string $city = null, string $state = null): Company
    {
        $company = new Company();
        $company->setName($name);
        $company->setCity($city);
        $company->setState($state);

        $this->em->persist($company);
        $this->em->flush();

        return $company;
    }

    private function createForm(): Form
    {
        $field = new Field();
        $field->setAlias('company-lookup');
        $field->setLabel('Company');
        $field->setType('companyLookup');

        $form = new Form();
        $form->setName('Company Lookup Test');
        $form->setAlias('company-lookup-test');
        $form->addField(0, $field);
        $field->setForm($form);

        $this->em->persist($field);
        $this->em->persist($form);
        $this->em->flush();

        return $form;
    }
}
