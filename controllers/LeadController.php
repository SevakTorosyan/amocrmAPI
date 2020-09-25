<?php

namespace app\controllers;

use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\LeadModel;
use app\components\services\ApiClientGettingService;
use Throwable;
use Yii;
use yii\web\Controller;

/**
 * Class LeadsConroller
 * @package app\controllers
 */
class LeadController extends Controller
{
    public const REQUEST_COUNT = 20;
    public const LEADS_COUNT   = 50;

    /** @var $apiClientGettingService ApiClientGettingService */
    public $apiClientGettingService;

    /**
     * UserController constructor.
     * @param $id
     * @param $module
     * @param ApiClientGettingService $apiClientGettingService
     * @param array $config
     */
    public function __construct($id, $module, ApiClientGettingService $apiClientGettingService, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->apiClientGettingService = $apiClientGettingService;
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $leadCollection = new LeadsCollection();
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $leadService = $apiClient->leads();
            $leadsEnded = false;
            $users = $apiClient->users()->get();
            $leads = $leadService->get();
            while (!$leadsEnded) {
                if ($leads->count() < 50) {
                    $leadsEnded = true;
                }
                foreach ($leads as $lead) {
                    $leadCollection->add($lead);
                }
                $leads = $leadService->nextPage($leads);
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
        $leadsCollectionArray = $leadCollection->toArray();
        foreach ($leadsCollectionArray as $leadKey => $lead) {
            $leadsCollectionArray[$leadKey]['responsible_user'] = ($users->getBy('id', $lead['responsible_user_id']))->getName();

            $responsibleUser = $users->getBy('id', $lead['created_by']);
            if ($lead['created_by'] === 0) {
                $leadsCollectionArray[$leadKey]['created_by'] = 'Robot';
            } elseif (!$responsibleUser) {
                $leadsCollectionArray[$leadKey]['created_by'] = 'Remote user';
            } else {
                $leadsCollectionArray[$leadKey]['created_by'] = ($users->getBy('id', $lead['created_by']))->getName();
            }
        }

        return $this->render('index', compact('leadsCollectionArray'));
    }

    /**
     * @return \yii\web\Response
     */
    public function actionCreate()
    {
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $leads = $apiClient->leads();
            for ($i = 0; $i < self::REQUEST_COUNT; $i++) {
                $leadsCollection = new LeadsCollection();
                for ($j = 0; $j < self::LEADS_COUNT; $j++) {
                    $lead = new LeadModel();
                    $leadNumber = $i * self::LEADS_COUNT + $j;
                    $lead->setName('Lead ' . $leadNumber);
                    $lead->setPrice(rand(100, 1000000));
                    $leadsCollection->add($lead);
                }
                $leads->add($leadsCollection);
            }

            echo "Создано " . self::REQUEST_COUNT * self:: LEADS_COUNT . " сделок";
        } catch (AmoCRMApiNoContentException $exception) {
        } catch (AmoCRMoAuthApiException $e) {
            Yii::$app->session->destroy();
            $this->redirect('/');
        } catch (AmoCRMApiException $e) {
            die('Ошибка API ' . $e->getDescription());
        } catch (Throwable $e) {
            die('Ошибка ' . $e->getMessage());
        }

        return $this->goBack('/lead');
    }

    /**
     * @return \yii\web\Response
     */
    public function actionUpdate()
    {
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $leadService = $apiClient->leads();

            $leadsEnded = false;

            $leads = $apiClient->leads()->get();
            $contacts = $apiClient->contacts()->get();
            $companies = $apiClient->companies()->get();
            $firstLeadId = $leads->first()->getId();
            $firstIteration = true;
            while (!$leadsEnded) {
                foreach ($leads as $lead) {
                    if ($lead->getId() === $firstLeadId && !$firstIteration) {
                        $leadsEnded = true;
                        break;
                    }
                    $currentCompany = $companies->offsetGet(rand(0, $companies->count()-1));
                    $currentContact = $contacts->offsetGet(rand(0, $contacts->count()-1));
                    $links = new LinksCollection();
                    $links->add($currentCompany);
                    $links->add($currentContact);
                    $leadService->link($lead, $links);
                }
                if (!$leadsEnded) {
                    $leads = $leadService->nextPage($leads);
                    $leads = $leadService->prevPage($leads);
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

        return $this->goBack('/lead');
    }
}
