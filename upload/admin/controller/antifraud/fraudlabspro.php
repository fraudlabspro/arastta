<?php
use Arastta\Component\Form\Form as AForm;

class ControllerAntifraudFraudLabsPro extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('antifraud/fraudlabspro');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && (isset($this->request->post['purge']))) {
            $this->db->query("TRUNCATE `" . DB_PREFIX . "order_fraudlabspro`");

            $this->session->data['success'] = $this->language->get('text_success_delete');

            $this->response->redirect($this->url->link('extension/extension', 'filter_type=antifraud&token=' . $this->session->data['token'], 'SSL'));
        } elseif (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('fraudlabspro_antifraud', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            if (isset($this->request->post['button']) && $this->request->post['button'] == 'save') {
                $this->response->redirect($this->url->link($this->request->get['route'], 'token=' . $this->session->data['token'], 'SSL'));
            }

            $this->response->redirect($this->url->link('extension/extension', 'filter_type=antifraud&token=' . $this->session->data['token'], 'SSL'));
        }

        // Add all language text
        $data = $this->language->all();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('antifraud/fraudlabspro', 'token=' . $this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/extension', 'filter_type=antifraud&token=' . $this->session->data['token'], 'SSL');

        $data['form_fields'] = $this->getFormFields($data['action']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->output('antifraud/fraudlabspro', $data));
    }

    protected function getFormFields($action) {
        $action = str_replace('amp;', '', $action);

        $status_text = array(
            'yes' => $this->language->get('text_enabled'),
            'no'  => $this->language->get('text_disabled')
        );

        $status['value'] = $this->config->get('fraudlabspro_antifraud_status', 0);
        $status['labelclass'] = 'radio-inline';

        $key['value'] = $this->config->get('fraudlabspro_antifraud_key', '');
        $key['placeholder'] = $this->language->get('entry_key');
        $key['required'] = $this->language->get('required');

        $score['value'] = $this->config->get('fraudlabspro_antifraud_score', '');
        $score['placeholder'] = $this->language->get('entry_score');

        $this->load->model('localisation/order_status');

        $order_option = array();
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();

        foreach ($order_statuses as $order_status) {
            $order_option[$order_status['order_status_id']] = $order_status['name'];
        }

        $order_text = array(
            'value' => $this->config->get('fraudlabspro_antifraud_order_status_id', 0),
            'selected'  => $this->config->get('fraudlabspro_antifraud_order_status_id', 0)
        );

        $review_order_text = array(
            'value' => $this->config->get('fraudlabspro_antifraud_review_status_id', 0),
            'selected'  => $this->config->get('fraudlabspro_antifraud_review_status_id', 0)
        );

        $approve_order_text = array(
            'value' => $this->config->get('fraudlabspro_antifraud_approve_status_id', 0),
            'selected'  => $this->config->get('fraudlabspro_antifraud_approve_status_id', 0)
        );

        $reject_order_text = array(
            'value' => $this->config->get('fraudlabspro_antifraud_reject_status_id', 0),
            'selected'  => $this->config->get('fraudlabspro_antifraud_reject_status_id', 0)
        );

        $simulate_ip['value'] = $this->config->get('fraudlabspro_antifraud_simulate_ip', '');
        $simulate_ip['placeholder'] = $this->language->get('entry_simulate_ip');

        $form = new AForm('form-fraudlabspro-antifraud', $action);

        $form->addElement(new Arastta\Component\Form\Element\HTML('<legend style="font-size:90%;">FraudLabs Pro is a fraud detection service. You can <a href="https://www.fraudlabspro.com/plan?ref=1730" target="_blank"><u>sign up here</u></a> for a free API Key.</legend>'));
        $form->addElement(new Arastta\Component\Form\Element\YesNo($this->language->get('entry_status'), 'fraudlabspro_antifraud_status', $status, $status_text));
        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_key'), 'fraudlabspro_antifraud_key', $key));
        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_score'), 'fraudlabspro_antifraud_score', $score));
        $form->addElement(new Arastta\Component\Form\Element\Select($this->language->get('entry_order_status'), 'fraudlabspro_antifraud_order_status_id', $order_option, $order_text));
        $form->addElement(new Arastta\Component\Form\Element\HTML('<legend>Rules Validation</legend>'));
        $form->addElement(new Arastta\Component\Form\Element\Select($this->language->get('entry_review_status'), 'fraudlabspro_antifraud_review_status_id', $order_option, $review_order_text));
        $form->addElement(new Arastta\Component\Form\Element\Select($this->language->get('entry_approve_status'), 'fraudlabspro_antifraud_approve_status_id', $order_option, $approve_order_text));
        $form->addElement(new Arastta\Component\Form\Element\Select($this->language->get('entry_reject_status'), 'fraudlabspro_antifraud_reject_status_id', $order_option, $reject_order_text));
        $form->addElement(new Arastta\Component\Form\Element\HTML('<legend>Testing Purpose</legend>'));
        $form->addElement(new Arastta\Component\Form\Element\Textbox($this->language->get('entry_simulate_ip'), 'fraudlabspro_antifraud_simulate_ip', $simulate_ip));

        return $form->render(true);
    }

    public function install() {
        $this->load->model('antifraud/fraudlabspro');

        $this->model_antifraud_fraudlabspro->install();
    }

    public function uninstall() {
        $this->load->model('antifraud/fraudlabspro');

        $this->model_antifraud_fraudlabspro->uninstall();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'antifraud/fraudlabspro')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['fraudlabspro_antifraud_key'])) {
            $this->error['warning'] = $this->language->get('error_key');
        }

        return !$this->error;
    }
}