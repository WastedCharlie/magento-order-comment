<?php
/**
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Magemaven
 * @package     Magemaven_OrderComment
 * @copyright   Copyright (c) 2011-2012 Sergey Storchay <r8@r8.com.ua>
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Magemaven_OrderComment_Model_Observer extends Varien_Object
{
    /**
     * Current comment
     *
     * @var bool|string
     */
    protected $_currentComment = false;

    /**
     * Save comment from agreement form to order
     *
     * @param $observer
     */
	public function saveOrderComment($observer)
	{
		$orderComment = Mage::app()->getRequest()->getPost('ordercomment');
		if (is_array($orderComment) && isset($orderComment['comment'])) {
			$comment = trim($orderComment['comment']);
			$comment = nl2br(Mage::helper('ordercomment')->stripTags($comment));
			// Attempting some server-side validation to compensate for the absence of client-side.
			$helper = Mage::helper('ordercomment');
			$minLength = $helper->getMinimumCommentLength();
			$maxLength = $helper->getMaximumCommentLength();
			if($minLength > 0 || $maxLength > 0){
				if(!Zend_Validate::is((string)$comment, 'StringLength', array('min' => $minLength, 'max' => $maxLength))){
					if($minLength && $maxLength){
						$exceptionMessage = $helper->__('Order comment should be between %s and %s characters.', $minLength, $maxLength);
					} elseif($minLength > 0){
						$exceptionMessage = $helper->__('Order comment should be at least %s characters.', $minLength);
					} elseif($maxLength > 0){
						$exceptionMessage = $helper->__('Order comment should be no longer than %s characters.', $maxLength);
					}
					/* Throwing an exception looks ugly, but should be handled by the try…catch around order->save.
					* Using the Mage_core exception because only that and Mage_Payment_Model_Info are handled, if you 
					* Mage_Payment_Model_Info will return to the Payment Information section. 
					* */
					throw Mage::exception('Mage_Core', $exceptionMessage, self::EXCEPTION_COMMENT_LENGTH); 
				}
			}

			if (!empty($comment)) {
				$order = $observer->getEvent()->getOrder(); 
				$order->setCustomerNoteNotify(true);
				$order->setCustomerNote($comment);
				$this->_currentComment = $comment;
			}
		}
	}

    /**
     * Show customer comment in 'My Account'
     *
     * @param $observer
     */
    public function setOrderCommentVisibility($observer)
    {
        if ($this->_currentComment) {
            $statusHistory = $observer->getEvent()->getStatusHistory();

            if ($statusHistory && $this->_currentComment == $statusHistory->getComment()) {
                $statusHistory->setIsVisibleOnFront(1);
            }
        }

    }
}
