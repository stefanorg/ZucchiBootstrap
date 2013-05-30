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
 * @category   Zend
 * @package    Zend_Form
 * @subpackage View
 * @copyright  Copyright (c) 2005-2013 Zucchi Limited (http://zucchi.co.uk)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace ZucchiBootstrap\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormRow;
use Zend\Form\View\Helper\FormLabel;
use Zend\Form\View\Helper\FormElementErrors;

use Zucchi\Form\View\Helper\FormElement;

/**
 * @category   Zend
 * @package    Zend_Form
 * @subpackage View
 * @copyright  Copyright (c) 2005-2013 Zucchi Limited (http://zucchi.co.uk)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class BootstrapRow extends FormRow
{
    /**
     * the style of form to generate
     * @var string
     */
    protected $formStyle = 'vertical';

    /**
     * templates to use for a bootstrap element
     *
     * %1$s - label open
     * %2$s - label
     * %3$s - label close
     * %4$s - element
     * %5$s - errors
     * %6$s - help
     * %7$s - status
     *
     * @var array
     */
    protected $defaultElementTemplates = array(
        'vertical' => '%1$s%2$s%3$s%4$s%5$s',
        'inline' => '%4$s%5$s',
        'search' => '%4$s%5$s',
        'horizontal' => '<div class="span12 field-box %6$s">
                            %1$s%2$s%3$s
                            %4$s
                            %5$s
                        </div>',
        'tableHead' => '<th>%2$s</th>',
        'tableRow' => '<td class="%6$s">%4$s</td>',

    );

    /**
     * templates used for rendering around an element string
     */
    protected $bootstrapTemplates = array(
        'help' => '<%1$s class="help-%2$s">%3$s</%1$s>',
        'prependAppend' => '<div class="%1$s">%2$s%3$s%4$s</div>',
    );

    /**
     * @var array
     */
    protected $labelAttributes;

    /**
     * @var FormLabel
     */
    protected $labelHelper;

    /**
     * @var FormElement
     */
    protected $elementHelper;

    /**
     * @var FormElementErrors
     */
    protected $elementErrorsHelper;

    /**
     * element types that act as grouped elements
     * @var array
     */
    protected $groupElements = array(
        'multi_checkbox',
        'multicheckbox',
        'radio',
    );

    /**
     * form styles that should be considered as compact
     * @var array
     */
    protected $compactFormStyles = array(
        'inline',
        'search',
        'tableRow',
    );


    /**
     * Utility form helper that renders a label (if it exists), an element and errors
     *
     * @param ElementInterface $element
     * @return string
     * @throws \Zend\Form\Exception\DomainException
     */
    public function render(ElementInterface $element)
    {

        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $labelHelper         = $this->getLabelHelper();
        $elementHelper       = $this->getElementHelper();
        $elementErrorsHelper = $this->getElementErrorsHelper();

        $label               = $element->getLabel();
        $elementErrorsHelper->setMessageOpenFormat('<div%s>')
                            ->setMessageSeparatorString('<br/>')
                            ->setMessageCloseString('</div>');
        $inputErrorClass = $this->getInputErrorClass();
        $elementErrors       = $elementErrorsHelper->render($element, array('class' => 'help-block'));

        $elementStatus       = $this->getElementStatus($element);
        $type                = $element->getAttribute('type');
        $bootstrapOptions    = $element->getOption('bootstrap');
        $formStyle           = (isset($bootstrapOptions['style'])) ? $bootstrapOptions['style'] : $this->getFormStyle();

        $labelOpen = $labelClose = $labelAttributes = ''; // initialise label variables
        $elementHelp = '';

        $markup = "";

        if ($type == 'hidden') {
            if ($formStyle != "tableHead" ) {
                $markup .= $elementHelper->render($element);
                $markup .= $elementErrorsHelper->render($element, array('class' => 'alert alert-error'));
            }
        } else {
            if (!empty($label)) {
                if (in_array($formStyle, $this->compactFormStyles)) {
                    $element->setAttribute('placeholder', $label);

                } else {

                    $label = $escapeHtmlHelper($label);
                    $labelAttributes = $element->getLabelAttributes();

                    if (empty($labelAttributes)) {
                        $labelAttributes = $this->labelAttributes;
                    }

                    $labelAttributes['class'] = isset($labelAttributes['class'])
                                              ? $labelAttributes['class'] . ' control-label'
                                              : 'control-label';

                    $labelOpen  = $labelHelper->openTag($labelAttributes);
                    $labelClose = $labelHelper->closeTag();
                }
            }

            if (in_array($type, $this->groupElements)) {
                $options = $element->getValueOptions();
                foreach ($options as $key => $optionSpec) {
                    if (is_string($optionSpec)) {
                        $tVal = $options[$key];
                        $options[$key] = array();
                        $options[$key]['label'] = $tVal;
                        $options[$key]['value'] = $key;
                        $options[$key]['label_attributes']['class'] = ($type == 'radio') ? 'radio' : 'checkbox';
                        $options[$key]['label_attributes']['class'] .= (in_array($formStyle, $this->compactFormStyles)) ? ' inline' : null;
                    } else {
                        $options[$key]['label_attributes']['class'] .= ($type == 'radio') ? 'radio' : 'checkbox';
                        $options[$key]['label_attributes']['class'] .= (in_array($formStyle, $this->compactFormStyles)) ? ' inline' : null;
                    }
                }
                $element->setAttribute('value_options', $options);
            }

            //$elementString       = $elementHelper->render($element);
            //var_dump($elementString);die();
            //element style
            $elementClassAttribute = $element->getAttribute('class');

            $element->setAttribute('class', empty($elementClassAttribute)
                                                ? 'span9'
                                                :  $element->getAttribute('class')
                                  );

             // var_dump($element);die();

            $elementString = $this->renderBootstrapOptions($element, $bootstrapOptions);

            if (!empty($label) && null !== ($translator = $this->getTranslator())) {
                $label = $translator->translate(
                    $label, $this->getTranslatorTextDomain()
                );
            }

            $markup = sprintf($this->defaultElementTemplates[$formStyle],
                $labelOpen,
                $label,
                $labelClose,
                $elementString,
                $elementErrors,
                $elementStatus
            );
        }

        return $markup;
    }


    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param null|ElementInterface $element
     * @param null|string $labelPosition
     * @return string|FormRow
     */
    public function __invoke(
        ElementInterface $element = null,
        $formStyle = 'vertical',
        $labelPosition = null,
        $renderErrors = true
    ) {
        if (!$element) {
            return $this;
        }

        $this->setFormStyle($formStyle);

        if ($labelPosition !== null) {
            $this->setLabelPosition($labelPosition);
        }

        $this->setRenderErrors($renderErrors);

        return $this->render($element);
    }

    /**
     * set the style of bootstrap form
     *
     * @param string $style
     * @return \Zucchi\Form\View\Helper\BootstrapRow
     */
    public function setFormStyle($style)
    {
        $this->formStyle = $style;
        return $this;
    }

    /**
     * get the current form style
     *
     * @return string
     */
    public function getFormStyle()
    {
        return $this->formStyle;
    }

    /**
     * get a string representation of the elements status
     *
     * @param ElementInterface $element
     * @return string
     */
    public function getElementStatus(ElementInterface $element)
    {
        $status = '';
        if (count($element->getMessages())) {
            $status = ' error ';
        }
        return $status;
    }

    /**
     * set the template to use in rendering
     *
     * @param string $template
     * @return NULL|string:
     */
    public function getDefaultElementTemplate($style)
    {
        if (!isset($this->defaultElementTemplates[$style])) {
            return null;
        }
        return $this->defaultElementTemplates[$style];
    }

    /**
     * set the template for a specified style
     *
     * @param string $style
     * @param string $template
     * @return $this
     */
    public function setDefaultElementTemplate($style, $template)
    {
        $this->defaultElementTemplates[$style];
        return $this;
    }

    /**
     * Render "bootstrap" options
     *
     * @param string $elementString
     * @param ElementInterface $element
     * @param array|Traversable $options
     */
    public function renderBootstrapOptions($element, $options)
    {
        $escapeHtmlHelper    = $this->getEscapeHtmlHelper();
        $elementHelper       = $this->getElementHelper();

        if (isset($options['help'])) {
            $help = $options['help'];
            $template = $this->bootstrapTemplates['help'];
            $style = 'placeholder';
            $content = '';
            if (is_array($help)) {
                if (isset($help['style'])) {
                    $style = $help['style'];
                }
                if (isset($help['content'])) {
                    $content = $help['content'];
                }
            } else {

                $content = $help;
            }

            if (null !== ($translator = $this->getTranslator())) {
                $content = $translator->translate(
                    $content, $this->getTranslatorTextDomain()
                );
            }

            switch ($style) {
                case 'inline':
                case 'block':
                    $elementString = $elementHelper->render($element);
                    $elementString .= sprintf($template,
                        ($style == 'block' ? 'p' : 'span'),
                        $style,
                        $content
                    );
                    break;
                default:
                    //placeholder
                    $element->setAttribute('placeholder', $content);
                    $elementString = $elementHelper->render($element);
                    break;
            }
            return $elementString;
        }

        if (isset($options['prepend']) || isset($options['append'])) {
            $template = $this->bootstrapTemplates['prependAppend'];
            $class = '';
            $prepend = '';
            $append = '';
            if (isset($options['prepend'])) {
                $class .= 'input-prepend ';
                if (!is_array($options['prepend'])) {
                    $options['prepend'] = (array) $options['prepend'];
                }
                foreach ($options['prepend'] AS $p) {
                    $prepend .= '<span class="add-on">' . $escapeHtmlHelper($p) . '</span>';
                }
            }
            if (isset($options['append'])) {
                $class .= 'input-append ';
                if (!is_array($options['append'])) {
                    $options['append'] = (array) $options['append'];
                }
                foreach ($options['append'] AS $a) {
                    $append .= '<span class="add-on">' . $escapeHtmlHelper($a) . '</span>';
                }
            }

            $elementString = sprintf($template,
                $class,
                $prepend,
                $elementString,
                $append);

        }

        return isset($elementString) ? $elementString : $elementHelper->render($element);

    }

    /**
     * Retrieve the FormElement helper
     *
     * @return FormElement
     */
    protected function getElementHelper()
    {
        if ($this->elementHelper) {
            return $this->elementHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->elementHelper = $this->view->plugin('form_element');
        }

        if (!$this->elementHelper instanceof FormElement) {
            $this->elementHelper = new FormElement();
        }

        return $this->elementHelper;
    }

}
