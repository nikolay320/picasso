<?php
abstract class Sabai_Addon_Form_Controller extends Sabai_Controller
{
    protected $_submitable = true, $_submitButtons = array(),
        $_ajaxSubmit, $_ajaxCancelType = 'hide', $_ajaxCancelUrl, $_ajaxOnCancel = 'function(target){}',
        $_ajaxOnSuccess, $_ajaxOnSuccessFlash = false, $_ajaxOnError, $_ajaxOnContent,
        $_cancelUrl, $_cancelWeight = 99, $_successFlash;

    protected function _doExecute(Sabai_Context $context)
    {
        // Initialize form storage bin
        $form_storage = array();
        // Check if form build ID has been sent in the request
        if ($form_build_id = $context->getRequest()->asStr(Sabai_Addon_Form::FORM_BUILD_ID_NAME, null)) {
            if (null === ($form_storage = $this->getAddon('Form')->getFormStorage($form_build_id))
                || !is_array($form_storage)
            ) {
                $form_storage = array();
            }
        }

        // Fetch form settings
        if (!$form_settings = $this->_getFormSettings($context, $form_build_id, $form_storage)) {
            // Set error message if not set and the returned value is false
            if ($form_settings === false && !$context->isError()) {
                $context->setError();
            }

            return;
        }

        // Build the form
        $form = $this->Form_Build($form_settings);

        // Validate form and submit
        if ($this->_submitable) {
            if ($form->submit($context->getRequest()->getParams())
                && !$context->isError()
                && !$form->rebuild
            ) { 
                if ($form->redirect) {
                    // Redirecting to another site, but should be redirecting back to the form, so do not clear storage here
                    $context->setView()
                        ->addTemplate('form_redirect')
                        ->setAttributes(array(
                            'url' => $form->redirect,
                            'message' => $form->redirectMessage ? $form->redirectMessage : __('Redirecting...', 'sabai'),
                        ));
                } else {
                    if (!empty($form->settings['#enable_storage'])) {
                        // Clear form storage
                        $this->getAddon('Form')->clearFormStorage($form->settings['#build_id']);
                    }
                    if (!$context->isRedirect()
                        && !$context->isSuccess()
                        && !($context->isView() && $context->hasTemplate())
                    ) {
                        $context->setSuccess();
                    }
                    if (isset($this->_successFlash) && $context->isSuccess()) {
                        $context->addFlash($this->_successFlash);
                    }
                }
                return;
            }
            // If error is set, clear form storage and do not display the form
            if ($context->isError()) {
                if (!empty($form->settings['#enable_storage'])) {
                    $this->getAddon('Form')->clearFormStorage($form->settings['#build_id']);
                }

                return;
            }
            $context->setView();
        }
        
        $form->settings['#js'] = isset($form->settings['#js']) ? (array)$form->settings['#js'] : array();
        if ($this->_ajaxSubmit) {
            $form->settings['#js'][] = $this->_getAjaxFormScript($context, empty($form->settings['#action']) ? (string)$this->Url($context->getRoute()) : $form->settings['#action']);
        } else {
            $form->settings['#js'][] = $this->_getFormScript($context);
        }
        $context->form = $form;
        if (!$context->hasTemplate()) {
            $context->addTemplate('form_form');
        }
        
        $this->Action('form_view', array($form));
    }

    final protected function _getFormSettings(Sabai_Context $context, $formBuildId, array &$formStorage)
    {
        // Load the form settings
        $form = $this->_doGetFormSettings($context, $formStorage);

        // Make sure an array is returned by the _getForm() method if displaying a form
        if (!is_array($form)) return $form;
        
        // Get all inherited class names
        if (!isset($form['#inherits'])) {
            $form['#inherits'] = array();
        }
        $class = get_class($this);
        while (__CLASS__ !== $class = get_parent_class($class)) {
            $form['#inherits'][] = $this->ControllerName($class);
        }

        // Auto define form name if not alreaady set, otherwise add to #inherits
        if (!isset($form['#name']) || strlen($form['#name']) === 0) {
            $form['#name'] = $this->ControllerName(get_class($this));
        } else {
            $form['#inherits'][] = $this->ControllerName(get_class($this));
        }
        
        // Initialize some required form properties
        if (isset($formBuildId)) $form['#build_id'] = $formBuildId;
        $form['#initial_storage'] = $formStorage;
        if (!isset($form['#action'])) {
            $form['#action'] = $this->Url($context->getRoute());
        }

        // Create form cancel link
        $cancel_link = null;
        if ($context->getRequest()->isXhr()
            && ($ajax_param = $context->getRequest()->isAjax())
            && $ajax_param !== '#sabai-content'
            && $ajax_param !== '#sabai-inline-content'
        ) {
            if (!isset($this->_ajaxSubmit)) {
                $this->_ajaxSubmit = true;
            }
            if (strpos($ajax_param, '#sabai-modal') !== 0 // no cancel link for modal
                && $this->_ajaxCancelType
                && $this->_ajaxCancelType != 'none'
            ) {
                // Create cancel link that will close the form
                $cancel_link = $this->_getAjaxCancelLink($ajax_param);
            }
            if ($this->_ajaxOnSuccessFlash) {
                // Do not save flash messages for the next page load
                $context->setFlashEnabled(false);
            }
        } else {
            if (!isset($this->_ajaxSubmit)) {
                $this->_ajaxSubmit = false;
            }
            if (isset($this->_cancelUrl)) {
                $cancel_link = sprintf(
                    '<a href="%s" class="sabai-form-action form-cancel-link">%s</a>',
                    $this->Url($this->_cancelUrl),
                    __('cancel', 'sabai')
                );
            }
        }

        if ($this->_submitable) {            
            $submits = $this->Form_SubmitButtons($this->_submitButtons, array(array($this, 'submitForm'), array($context)));
            if (isset($cancel_link)) {
                $submits['cancel'] = array(
                    '#type' => 'markup',
                    '#markup' => $cancel_link,
                    '#weight' => $this->_cancelWeight,
                );
            }
            if (!isset($form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME])) {
                $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME] = $submits;
            } else {
                $form[Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME] += $submits;
            }
        }

        return $form;
    }

    private function _getAjaxCancelLink($ajaxParam)
    {
        // Create cancel link that will close the form only when the form is requested as partial content
        switch ($this->_ajaxCancelType) {
            case 'slide':
                return sprintf(
                    '<a class="form-cancel-link sabai-form-action" href="%1$s" onclick="jQuery(\'%1$s\').slideUp(\'fast\'); var callback = %3$s; callback.call(this, jQuery(\'%1$s\')); return false">%2$s</a>',
                    Sabai::h($ajaxParam), __('cancel', 'sabai'), str_replace('"', "'", $this->_ajaxOnCancel)
                );
            case 'fade':
                return sprintf(
                    '<a class="form-cancel-link sabai-form-action" href="%1$s" onclick="jQuery(\'%1$s\').fadeOut(\'fast\'); var callback = %3$s; callback.call(this, jQuery(\'%1$s\')); return false">%2$s</a>',
                    Sabai::h($ajaxParam), __('cancel', 'sabai'), str_replace('"', "'", $this->_ajaxOnCancel)
                );
            case 'remote':
                if (isset($this->_cancelUrl)) {
                    return $this->LinkToRemote(
                        __('cancel', 'sabai'),
                        $ajaxParam,
                        $this->_cancelUrl,
                        array('url' => $this->_ajaxCancelUrl, 'scroll' => true),
                        array('class' => 'form-cancel-link sabai-form-action')
                    );
                }
            default:
                return sprintf(
                    '<a class="form-cancel-link sabai-form-action" href="%1$s" onclick="jQuery(\'%1$s\').hide(\'fast\'); var callback = %3$s; callback.call(this, jQuery(\'%1$s\')); return false">%2$s</a>',
                    Sabai::h($ajaxParam), __('cancel', 'sabai'), str_replace('"', "'", $this->_ajaxOnCancel)
                );
        }
    }
    
    protected function _getFormScript(Sabai_Context $context)
    {
        return 'jQuery(document).ready(function($) {
    var form = $("#' . Sabai_Addon_Form::FORM_ID_PLACEHOLDER . '");
    form.find("input[type=submit]:not(:disabled), input[type=image]").click(function(e){
        form.append($(this).clone().attr("type", "hidden"));
    }).end().submit(function() {
        form.find("input[type=\"submit\"]").prop("disabled", true);
    });
});';
    }
    
    protected function _getAjaxFormScript(Sabai_Context $context, $url)
    {
        return sprintf('
jQuery(document).ready(function($){
    var form = $("#' . Sabai_Addon_Form::FORM_ID_PLACEHOLDER . '");
    form.find("input[type=submit]:not(:disabled), input[type=image]").click(function(e){
        var $this = $(this);

        // Uploading file via ajax is not supported.
        form.find("input[type=file]").each(function(){
            if ($(this).attr("value")) return true;
        });

        // Form.serialize() will not include the value of submit button so append the value as a hidden element.
        form.append($this.clone().attr("type", "hidden"));

        SABAI.ajax({trigger: $this, type: form.attr("method"), container: "%2$s", modalWidth: 0, url: "%3$s", onSuccess: %4$s,
            onError: %5$s, onContent: %6$s, data: form.serialize(), scroll: true, onSuccessFlash: %7$s, target: "%8$s"
        });

        e.preventDefault();
    });
});',
            Sabai_Addon_Form::FORM_ID_PLACEHOLDER,
            Sabai::h($context->getContainer()),
            ($pos = strpos($url, '#')) ? substr($url, 0, $pos) : $url,
            $this->_ajaxOnSuccess ? $this->_ajaxOnSuccess : 'null',
            $this->_ajaxOnError ? $this->_ajaxOnError : 'null',
            $this->_ajaxOnContent ? $this->_ajaxOnContent : 'null',
            $this->_ajaxOnSuccessFlash ? 'true' : 'false',
            ($target = $context->getTarget()) ? Sabai::h($target) : ''
        );
    }
    
    protected function _makeTableSortable(Sabai_Context $context, array &$element, array $sortableHeaders, array $timestampHeaders = array(), $currentSort = null, $currentOrder = 'DESC', array $params = array())
    {
        if ($element['#type'] !== 'tableselect') return;
        
        foreach ($sortableHeaders as $header_name) {
            if (is_array($header_name)) {
                $no_escape = true;
                $title = @$header_name['title'];
                $header_name = $header_name['name'];
            } else {
                $no_escape = false;
            }
            if (!isset($element['#header'][$header_name])) continue;
            if (!is_array($element['#header'][$header_name])) {
                $element['#header'][$header_name] = array(
                    'label' => $element['#header'][$header_name],
                );
            }
            $header_label = $element['#header'][$header_name]['label'];
            $attr = array(
                'title' => isset($title) ? $title : sprintf(__('Sort by %s', 'sabai'), $header_label),
                'data-modal-title' => '', // keep current modal title
            );
            if (!$no_escape) {
                $header_label = Sabai::h($header_label);
            }
            $_params = array('sort' => $header_name) + $params;
            $options = array('no_escape' => true);
            if ($currentSort === $header_name) {
                if (in_array($header_name, $timestampHeaders)) {
                    $class = $currentOrder === 'ASC' ? 'down' : 'up';
                } else {
                    $class = $currentOrder === 'ASC' ? 'up' : 'down';
                }
                $header_label = $header_label . ' <i class="fa fa-sort-' . $class . '"></i>';
                $_params['order'] = $currentOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                $header_label = $header_label . ' <i class="fa fa-sort"></i>';
            }
            $element['#header'][$header_name]['label'] = $this->LinkToRemote(
                $header_label,
                $context->getContainer(),
                $this->Url((string)$context->getRoute(), $_params),
                $options,
                $attr
            );
        }
    }

    public function submitForm(Sabai_Addon_Form_Form $form, Sabai_Context $context)
    {
    }

    abstract protected function _doGetFormSettings(Sabai_Context $context, array &$formStorage);
}