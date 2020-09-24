<?php

namespace app\components\services;

use AmoCRM\Collections\CustomFields\CustomFieldEnumsCollection;
use AmoCRM\Models\CustomFields\EnumModel;
use AmoCRM\Models\CustomFields\MultiselectCustomFieldModel;
use app\controllers\ContactController;

/**
 * Class MultiselectCustomFieldCreatingService
 * @package app\components\services
 */
class MultiselectCustomFieldCreatingService
{
    /**
     * @return MultiselectCustomFieldModel
     */
    public function createCustomField()
    {
        $multiSelectModel = new MultiselectCustomFieldModel();
        $multiSelectModel->setName('Язык');
        $multiSelectModel->setEnums(
            (new CustomFieldEnumsCollection())
                ->add(
                    (new EnumModel())
                        ->setValue('Английский')
                        ->setSort(10)
                )
                ->add(
                    (new EnumModel())
                        ->setValue('Русский')
                        ->setSort(20)
                )
                ->add(
                    (new EnumModel())
                        ->setValue('Испанский')
                        ->setSort(30)
                )
        );
        $multiSelectModel->setCode(ContactController::CUSTOM_FIELD_CODE);
        $multiSelectModel->setEntityType('contacts');
        $multiSelectModel->setIsApiOnly(false);
        $multiSelectModel->setIsVisible(true);
        return $multiSelectModel;
    }
}
