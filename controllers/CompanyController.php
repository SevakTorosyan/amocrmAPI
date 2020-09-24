<?php

namespace app\controllers;

use AmoCRM\Collections\CompaniesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\CompanyModel;
use app\components\services\ApiClientGettingService;
use Throwable;
use Yii;
use yii\web\Controller;

/**
 * Class CompanyController
 * @package app\controllers
 */
class CompanyController extends Controller
{
    public const REQUEST_COUNT = 20;
    public const COMPANY_COUNT = 50;

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
        $companiesCollection = new CompaniesCollection();
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $companyService = $apiClient->companies();
            $companiesEnded = false;
            $users = $apiClient->users()->get();
            $companies = $companyService->get();
            while (!$companiesEnded) {
                if ($companies->count() < 50) {
                    $companiesEnded = true;
                }
                foreach ($companies as $company) {
                    $companiesCollection->add($company);
                }
                $companies = $companyService->nextPage($companies);
            }
        } catch (AmoCRMApiNoContentException $exception) {
        } catch (AmoCRMoAuthApiException $e) {
            Yii::$app->session->destroy();
            $this->redirect('/');
        } catch (AmoCRMApiException $e) {
            die('Ошибка API ' . $e->getMessage());
        } catch (Throwable $e) {
            die('Ошибка ' . $e->getMessage());
        }
        $companiesArray = $companiesCollection->toArray();
        foreach ($companiesArray as $companyKey => $company) {
            $companiesArray[$companyKey]['responsible_user'] = ($users->getBy('id', $company['responsible_user_id']))->getName();
            if ($company['responsible_user_id'] === $company['created_by']) {
                $companiesArray[$companyKey]['created_by'] = $companiesArray[$companyKey]['responsible_user'];
            } else {
                $companiesArray[$companyKey]['created_by'] = ($users->getBy('id', $company['created_by']))->getName();
            }
        }

        return $this->render('index', compact('companiesArray'));
    }

    /**
     * @return string
     */
    public function actionCreate()
    {
        try {
            $apiClient = $this->apiClientGettingService->getApiClient();
            $companies = $apiClient->companies();
            for ($i = 0; $i < self::REQUEST_COUNT; $i++) {
                $companiesCollection = new CompaniesCollection();
                for ($j = 0; $j < self::COMPANY_COUNT; $j++) {
                    $company = new CompanyModel();
                    $companyNumber = $i * self::COMPANY_COUNT + $j;
                    $company->setName('Company ' . $companyNumber);
                    $companiesCollection->add($company);
                }
                $companies->add($companiesCollection);
            }
        } catch (AmoCRMoAuthApiException $e) {
            Yii::$app->session->destroy();
            $this->redirect('/');
        } catch (AmoCRMApiException $e) {
            die('Ошибка API ' . $e->getDescription());
        } catch (Throwable $e) {
            die('Ошибка ' . $e->getMessage());
        }

        return $this->redirect('/company');
    }
}
