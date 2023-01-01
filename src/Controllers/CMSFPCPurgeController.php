<?php

declare(strict_types=1);

namespace WeDevelop\FPCPurge\Controllers;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use WeDevelop\FPCPurge\FPCPurgeConfig;
use WeDevelop\FPCPurge\FPCPurgeService;

final class CMSFPCPurgeController extends LeftAndMain
{
    private static $url_segment = 'fpc-purge';

    private static $menu_title = 'FPC Purge';

    private static $menu_priority = -100;

    private static $allowed_actions = [
        'EditForm',
    ];

    public function getEditForm($id = null, $fields = null)
    {
        $actions = new FieldList();
        $actions->push(
            FormAction::create('doPurge', 'Purge Cache')
                ->addExtraClass('btn-primary')
                ->setUseButtonTag(true)
        );

        $form = Form::create(
            $this,
            'EditForm',
            new FieldList(),
            $actions
        )->setHTMLID('Form_EditForm');

        $form->unsetValidator();
        $form->addExtraClass('cms-edit-form');
        $form->addExtraClass('root-form');
        $form->setTemplate($this->getTemplatesWithSuffix('_EditForm'));
        $form->setAttribute('data-pjax-fragment', 'CurrentForm');

        return $form;
    }

    public function doPurge($data, Form $form)
    {
        $request = $this->getRequest();
        $response = $this->getResponseNegotiator()->respond($request);

        if (!FPCPurgeConfig::isEnabled()) {
            $response->setStatusCode(400);
            $response->addHeader('X-Status', rawurlencode('FPC is disabled.'));
            return $response;
        }

        FPCPurgeService::purge();

        $response->addHeader('X-Status', rawurlencode('Cache purged!'));
        return $response;
    }
}
