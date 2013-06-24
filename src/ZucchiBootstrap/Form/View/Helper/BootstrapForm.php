<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zucchi
 * @package    Zucchi_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2013 Zucchi Limited (http://zucchi.co.uk)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZucchiBootstrap\Form\View\Helper;

use \Zend\View\Helper\AbstractHelper;
use \Zend\Form\Form;
use \Zend\Form\Fieldset;

/**
 * @package    Zucchi_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2013 Zucchi Limited (http://zucchi.co.uk)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class BootstrapForm extends AbstractHelper
{
    protected $collectionHelper = 'bootstrapCollection';
    protected $rowHelper        = 'bootstrapRow';


    public function __invoke(Form $form, $style = 'vertical')
    {
        // var_dump($form);die();
        $form->setAttributes(array(
            'class' => $form->getAttribute('class') . ' inline-input'
        ));

        if ($style) {
            $form->setAttribute('class', $form->getAttribute('class') . ' form-' . $style);
        }
        $form->prepare();

        $output = '';

        $output .= $this->view->form()->openTag($form);

        $elements = $form->getIterator();
        foreach ($elements as $key => $element) {
            if ($element instanceof Fieldset) {

                $helper = $this->getCollectionHelper();
                $output .= $this->view->{$helper}($element, $style);
            } else {
                $helper = $this->getRowHelper();
                
                $output .= $this->view->{$helper}($element, $style);
            }
        }

        $output .= $this->view->form()->closeTag($form);

        return $output;
    }

    /**
     * Gets the value of collectionHelper.
     *
     * @return mixed
     */
    public function getCollectionHelper()
    {
        return $this->collectionHelper;
    }

    /**
     * Sets the value of collectionHelper.
     *
     * @param mixed $collectionHelper the collectionHelper
     *
     * @return self
     */
    public function setCollectionHelper($collectionHelper)
    {
        $this->collectionHelper = $collectionHelper;

        return $this;
    }

    /**
     * Gets the value of rowHelper.
     *
     * @return mixed
     */
    public function getRowHelper()
    {
        return $this->rowHelper;
    }

    /**
     * Sets the value of rowHelper.
     *
     * @param mixed $rowHelper the rowHelper
     *
     * @return self
     */
    public function setRowHelper($rowHelper)
    {
        $this->rowHelper = $rowHelper;

        return $this;
    }
}
