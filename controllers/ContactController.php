<?php

namespace app\controllers;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultiselectCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultiselectCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultiselectCustomFieldValueModel;
use app\components\services\ApiClientGettingService;
use app\components\services\MultiselectCustomFieldCreatingService;
use Throwable;
use Yii;
use yii\web\Controller;

/**
 * Class UserController
 * @package app\controllers
 */
class ContactController extends Controller
{
    public const REQUEST_COUNT          = 20;
    public const CONTACT_COUNT          = 50;
    public const CUSTOM_FIELD_CODE      = 'MULTISELECT';
    public const VALUES_FOR_MULTISELECT = [
        'Английский',
        'Русский',
        'Испанский',
    ];

    /** @var $apiClientGettingService ApiClientGettingService */
    public $apiClientGettingService;

    /** @var $multiselectCreatingService MultiselectCustomFieldCreatingService */
    public $multiselectCreatingService;

    /**
     * ContactController constructor.
     * @param $id
     * @param $module
     * @param ApiClientGettingService $apiClientGettingService
     * @param MultiselectCustomFieldCreatingService $multiselectCreatingService
     * @param array $config
     */
    public function __construct(
        $id,
        $module,
        ApiClientGettingService $apiClientGettingService,
        MultiselectCustomFieldCreatingService $multiselectCreatingService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->apiClientGettingService = $apiClientGettingService;
        $this->multiselectCreatingService = $multiselectCreatingService;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $contactsCollection = new ContactsCollection();
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $contactService = $apiClient->contacts();
            $contactsEnded = false;
            $users = $apiClient->users()->get();
            $contacts = $contactService->get();
            while (!$contactsEnded) {
                if ($contacts->count() < 50) {
                    $contactsEnded = true;
                }
                foreach ($contacts as $contact) {
                    $contactsCollection->add($contact);
                }
                $contacts = $contactService->nextPage($contacts);
            }
        } catch (AmoCRMApiNoContentException $exception) {
        } catch (AmoCRMoAuthApiException $e) {
            Yii::$app->session->destroy();
            $this->redirect('/');
        } catch (AmoCRMApiException $e) {
            die('Ошибка API ' . $e->getDescription());
        } catch (Throwable $e) {
            die('Ошибка ' . $e->getMessage());
        }
        $contactsArray = [];
        /** @var ContactModel $contact */
        foreach ($contactsCollection as $contactKey => $contact) {
            $contactsArray[] = $contact->toArray();
            $contactsArray[count($contactsArray)-1]['responsible_user'] = ($users->getBy('id', $contact->getResponsibleUserId()))->getName();
            if ($contact->getResponsibleUserId() === $contact->getCreatedBy()) {
                $contactsArray[count($contactsArray)-1]['created_by'] = $contactsArray[count($contactsArray)-1]['responsible_user'];
            } else {
                $contactsArray[count($contactsArray)-1]['created_by'] = ($users->getBy('id', $contact->getCreatedBy()))->getName();
            }
            if ($contact->getCustomFieldsValues()) {
                foreach ($contact->getCustomFieldsValues() as $customFieldsValues) {
                    /** @var MultiselectCustomFieldValueModel $customFieldValue */
                    foreach ($customFieldsValues->getValues() as $customFieldValue) {
                        $contactsArray[count($contactsArray)-1]['custom_field_values'][] = $customFieldValue->getValue();
                    }
                }
            }
        }
        return $this->render('index', compact('contactsArray'));
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCreate()
    {
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $contacts = $apiClient->contacts();
            for ($i = 0; $i < self::REQUEST_COUNT; $i++) {
                $contactCollection = new ContactsCollection();
                for ($j = 0; $j < self::CONTACT_COUNT; $j++) {
                    $contact = new ContactModel();
                    $contact->setFirstName(Yii::$app->security->generateRandomString(10));
                    $contact->setLastName(Yii::$app->security->generateRandomString(10));
                    $contactCollection->add($contact);
                }
                $contacts->add($contactCollection);
            }
        } catch (AmoCRMoAuthApiException $e) {
            Yii::$app->session->destroy();
            $this->redirect('/');
        } catch (AmoCRMApiException $e) {
            die('Ошибка API ' . $e->getDescription());
        } catch (Throwable $e) {
            die('Ошибка ' . $e->getMessage());
        }

        return $this->goBack('/contact');
    }

    /**
     * @return \yii\web\Response
     */
    public function actionUpdate()
    {
        $apiClient = $this->apiClientGettingService->getApiClient();
        try {
            $handledContactCount = 0;
            $contactsService = $apiClient->contacts();
            $contacts = $contactsService->get();
            $contactsEnded = false;
            $firstContactId = $contacts->first()->getId();
            $firstIteration = true;

            while (!$contactsEnded) {
                $contactsCollection = new ContactsCollection();
                foreach ($contacts as $contact) {
                    if ($contact->getId() === $firstContactId && !$firstIteration) {
                        $contactsEnded = true;
                        break;
                    }
                    $optionsCount = rand(1, count(self::VALUES_FOR_MULTISELECT));
                    $multiselectField = (new MultiselectCustomFieldValuesModel())->setFieldCode(self::CUSTOM_FIELD_CODE);
                    $multiselectValueCollection = new MultiselectCustomFieldValueCollection();
                    for ($i = 0; $i < $optionsCount; $i++) {
                        $multiselectValueCollection->add(
                            (new MultiselectCustomFieldValueModel())->setValue(self::VALUES_FOR_MULTISELECT[$i])
                        );
                    }
                    $multiselectField->setValues($multiselectValueCollection);
                    $fieldsCollection = new CustomFieldsValuesCollection();
                    $fieldsCollection->add($multiselectField);
                    $contact->setCustomFieldsValues($fieldsCollection);
                    $contactsCollection->add($contact);
                    $handledContactCount++;
                }
                if (!$contactsEnded) {
                    $contactsService->update($contactsCollection);
                    $contacts = $apiClient->contacts()->nextPage($contacts);
                    $contacts = $apiClient->contacts()->prevPage($contacts);
                }
                $firstIteration = false;
            }
        } catch (AmoCRMApiNoContentException $exception) {
        } catch (AmoCRMoAuthApiException $e) {
            Yii::$app->session->destroy();
            $this->redirect('/');
        } catch (AmoCRMApiException $e) {
            die('Ошибка API ' . $e->getDescription());
        } catch (Throwable $e) {
            die('Ошибка ' . $e->getMessage());
        }

        return $this->goBack('/contact');
    }

    /**
     * @return string
     * @throws \AmoCRM\Exceptions\AmoCRMApiException
     * @throws \AmoCRM\Exceptions\AmoCRMoAuthApiException
     * @throws \AmoCRM\Exceptions\InvalidArgumentException
     */
    public function actionAddField()
    {

        $apiClient = $this->apiClientGettingService->getApiClient();
        $customFields = $apiClient->customFields('contacts')->get();
        $customFieldModel = $customFields->getBy('code', self::CUSTOM_FIELD_CODE);
        if (!$customFieldModel) {
            $customFieldModel = $this->multiselectCreatingService->createCustomField();
            $apiClient->customFields('contacts')->addOne($customFieldModel);
        }

        return $this->redirect('update');
    }
}
