<?php

namespace Biin2013\DcatAdminTools\Foundation\Grid\Actions;

use Biin2013\DcatAdminTools\Foundation\Grid;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Actions\Delete as Base;

class Delete extends Base
{
    public function render(): string
    {
        if ($this->parent instanceof Grid) {
            $message = $this->parent->getDeleteMessage($this->row);
            if ($title = $this->parent->getDeleteMessageTitle($this->row)) {
                $message = $title . $this->parent->getDeleteMessageSeparator() . $message;
            }
        } else {
            $message = "ID - {$this->getKey()}";
        }

        $this->setHtmlAttribute([
            'data-url' => $this->url(),
            'data-message' => $message,
            'data-action' => 'delete',
            'data-redirect' => $this->redirectUrl(),
        ]);

        return $this->copyRender();
    }

    private function copyRender(): string
    {
        if (!$this->allowed()) {
            return '';
        }

        $this->prepareHandler();

        $this->setUpHtmlAttributes();

        if ($script = $this->script()) {
            Admin::script($script);
        }

        return $this->html();
    }
}