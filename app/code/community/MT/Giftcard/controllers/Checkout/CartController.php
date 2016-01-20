<?php

class MT_Giftcard_Checkout_CartController
    extends Mage_Core_Controller_Front_Action
{

    public function clearGiftCardCodeAjaxAction()
    {
        $error = false;
        $success = false;
        try {
            $quoteId = $this->_getQuote()->getId();
            $giftCardQuote = Mage::getModel('giftcard/quote');

            if ($giftCardQuote->removeAllGiftCardFromQuote($quoteId)) {
                $success = $this->__('Gift cards was removed.');
            }
        } catch (Mage_Core_Exception $e) {
            $error = $e->getMessage();
        } catch (Exception $e) {
            $error = $this->__('Cannot delete the gift cards.');
        }

        $content = $this->getLayout()->createBlock('giftcard/payment_giftcard_form_giftcard','payment_giftcard_form_giftcard',array(
            'error' => $error,
            'success' => $success,
        ))->renderView();

        $this->getResponse()->setBody(json_encode(array(
            'content' => $content
        )));
    }

    public function removeGiftCardCodeAjaxAction()
    {
        $giftCardCode = $this->getRequest()->getParam('giftcard_code');
        $error = false;
        $success = false;

        if (!empty($giftCardCode)) {
            try {
                $quoteId = $this->_getQuote()->getId();
                $giftCardQuote = Mage::getModel('giftcard/quote');
                if ($giftCardQuote->removeGiftCardByCode($quoteId, $giftCardCode)) {
                    $success = $this->__('Gift cards was removed.');
                }
            } catch (Mage_Core_Exception $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                $error = $this->__('Cannot delete the gift cards.');
            }
        }

        $content = $this->getLayout()->createBlock('giftcard/payment_giftcard_form_giftcard','payment_giftcard_form_giftcard',array(
            'error' => $error,
            'success' => $success,
        ))->renderView();

        $this->getResponse()->setBody(json_encode(array(
            'content' => $content
        )));
    }

    public function addGiftCardCodeAjaxAction()
    {
        $error = false;
        $success = '';

        if ($this->_getCart()->getQuote()->getItemsCount()) {
            $giftCardCode = (string) $this->getRequest()->getParam('giftcard_code');
            try {
                $quoteId = $this->_getQuote()->getId();
                $giftCardQuote = Mage::getModel('giftcard/quote');
                if (!$giftCardQuote->addGiftCardByCode($quoteId, $giftCardCode)) {
                    $error = $this->__('Gift card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
                }
            } catch (Mage_Core_Exception $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                $error = $this->__('Cannot apply the gift card.');
                Mage::logException($e);
            }
        }

        $content = $this->getLayout()->createBlock('giftcard/payment_giftcard_form_giftcard','payment_giftcard_form_giftcard',array(
            'error' => $error,
            'success' => $success,
        ))->renderView();

        $this->getResponse()->setBody(json_encode(array(
            'content' => $content
        )));
    }

    public function giftCardAjaxAction()
    {
        $error = false;

        if ($this->_getCart()->getQuote()->getItemsCount()) {
            $giftCardCode = (string) $this->getRequest()->getParam('giftcard_code');
            $helper = Mage::helper('giftcard');
            try {
                $quoteId = $this->_getQuote()->getId();
                $giftCardQuote = Mage::getModel('giftcard/quote');
                if (!$giftCardQuote->addGiftCardByCode($quoteId, $giftCardCode)) {
                    $error = $this->__('Gift card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode));
                }
            } catch (Mage_Core_Exception $e) {
                $error = $e->getMessage();
            } catch (Exception $e) {
                $error = $this->__('Cannot apply the gift card.');
                Mage::logException($e);
            }
        }

        $response = $this->getLayout()->createBlock('giftcard/payment_giftcard_form_giftcard','payment_giftcard_form_giftcard',array(
            'error' => $error,
        ))->renderView();

        $this->getResponse()->setBody(json_encode(array(
            'content' => $response
        )));
    }

    public function giftCardPostAction()
    {
        if ($this->getRequest()->getParam('remove') == 1) {
            $this->_forward('giftCardRemove');
            return;
        }

        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $giftCardCode = (string) $this->getRequest()->getParam('giftcard_code');

        try {
            $quoteId = $this->_getQuote()->getId();
            $giftCardQuote = Mage::getModel('giftcard/quote');
            if ($giftCardQuote->addGiftCardByCode($quoteId, $giftCardCode)) {
                $this->_getSession()->addSuccess(
                    $this->__('Gift card "%s" was applied.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            } else {
                $this->_getSession()->addError(
                    $this->__('Gift card "%s" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode))
                );
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) { print_r($e);
            $this->_getSession()->addError($this->__('Error! Please contact with administrator'));
            Mage::logException($e);
        }

        $this->_goBack();
    }

    public function giftCardRemoveAction()
    {
        $removeCodes = $this->getRequest()->getParam('giftcard_remove');

        if (count($removeCodes) == 0) {
            $this->_goBack();
            $this->_getSession()->addError($this->__('Please select Gift Cards which want to remove.'));
            return;
        }

        try {
            $quoteId = $this->_getQuote()->getId();
            Mage::getModel('giftcard/quote')->removeGiftCardArrayFromQuote($quoteId, $removeCodes);
            $this->_getSession()->addSuccess(
                $this->__('Gift cards was removed.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot delete the gift cards.'));
            Mage::logException($e);
        }
        $this->_goBack();
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }
}
