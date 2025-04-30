<?php

namespace Biin2013\DcatAdminTools\Renders;

use Dcat\Admin\Support\LazyRenderable;
use ReflectionClass;
use ReflectionException;

class LazyDetail extends LazyRenderable
{
    /**
     * @throws ReflectionException
     */
    public function render()
    {
        $reflectionClass = new ReflectionClass($this->payload['class']);
        $controller = $reflectionClass->newInstance();

        $translation = $reflectionClass->getMethod('translation')
            ->invoke($controller, $this->payload['class']);
        app('admin.translator')->setPath($translation);

        $show = $reflectionClass->getMethod('detail')
            ->invoke($controller, $this->payload['id'] ?? $this->key);
        $show->panel()->title('');

        return $show->disableListButton()
            ->disableEditButton()
            ->disableDeleteButton();
    }
}