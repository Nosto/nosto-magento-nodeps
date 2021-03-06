<?php
/**
 * Copyright (c) 2019, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2019 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */



/**
 * Handles sending the Nosto_Operation_OrderConfirm confirmations to Nosto via the API.
 *
 * Nosto_Operation_OrderConfirm confirmations can be sent two different ways:
 * - matched orders; where we know the Nosto customer ID of the user who placed the Nosto_Operation_OrderConfirm
 * - un-matched orders: where we do not know the Nosto customer ID of the user who placed the Nosto_Operation_OrderConfirm
 *
 * The second option is a fallback and should be avoided as much as possible.
 */
class Nosto_Operation_OrderConfirm extends Nosto_Operation_AbstractAuthenticatedOperation
{
    /**
     * Nosto_Operation_OrderConfirm constructor.
     * @param Nosto_Types_Signup_AccountInterface $account
     * @param string $activeDomain
     */
    public function __construct(Nosto_Types_Signup_AccountInterface $account, $activeDomain = '')
    {
        parent::__construct($account, $activeDomain);
    }

    /**
     * Sends the Nosto_Operation_OrderConfirm confirmation to Nosto.
     *
     * @param Nosto_Types_Order_OrderInterface $order the placed Nosto_Operation_OrderConfirm model.
     * @param string|null $customerId the Nosto customer ID of the user who placed the OrderConfirm.
     * @return true on success.
     * @throws Nosto_Request_Http_Exception_AbstractHttpException
     */
    public function send(Nosto_Types_Order_OrderInterface $order, $customerId = null)
    {
        $request = new Nosto_Request_Api_ApiRequest();
        if (!empty($customerId)) {
            $request->setPath(Nosto_Request_Api_ApiRequest::PATH_ORDER_TAGGING);
            $replaceParams = array('{m}' => $this->account->getName(), '{cid}' => $customerId);
        } else {
            $request->setPath(Nosto_Request_Api_ApiRequest::PATH_UNMATCHED_ORDER_TAGGING);
            $replaceParams = array('{m}' => $this->account->getName());
        }
        if (is_string($this->activeDomain)) {
            $request->setActiveDomainHeader($this->activeDomain);
        }
        if (is_string($this->account->getName())) {
            $request->setNostoAccountHeader($this->account->getName());
        }
        $request->setReplaceParams($replaceParams);
        $response = $request->post($order);
        return self::checkResponse($request, $response);
    }
}
