<?php
/**
 * Copyright (c) 2019, Nosto_Nosto Solutions Ltd
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
 * @author Nosto_Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2019 Nosto_Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */



/**
 * Abstract base operation class to be used in recommendation related operations
 */
abstract class Nosto_Operation_Recommendation_AbstractOperation extends Nosto_Operation_AbstractAuthenticatedOperation
{
    const GRAPHQL_DATA_KEY = 'primary';

    private $previewMode = false;
    private $customerId;
    private $limit;

    /**
     * Builds the recommendation API request
     *
     * @return string
     */
    abstract public function getQuery();

    /**
     * Create and returns a new Graphql request object initialized with a content-type
     * of 'application/json' and the specified authentication token
     *
     * @return Nosto_Request_Graphql_GraphqlRequest the newly created request object.
     * @throws Nosto_NostoException if the account does not have the correct token set.
     * @throws Nosto_NostoException
     */
    protected function initGraphqlRequest()
    {
        $token = $this->account->getApiToken(Nosto_Request_Api_Token::API_GRAPHQL);
        if (is_null($token)) {
            throw new Nosto_NostoException('No API / Graphql token found for account.');
        }

        $request = new Nosto_Request_Graphql_GraphqlRequest();
        $request->setResponseTimeout($this->getResponseTimeout());
        $request->setConnectTimeout($this->getConnectTimeout());
        $request->setContentType(self::CONTENT_TYPE_APPLICATION_JSON);
        $request->setAuthBasic('', $token->getValue());
        $request->setUrl(Nosto_Nosto::getGraphqlBaseUrl() . Nosto_Request_Graphql_GraphqlRequest::PATH_GRAPH_QL);

        return $request;
    }


    /**
     * Removes line breaks from the string
     *
     * @return null|string|string[]
     */
    public function buildPayload()
    {
        return preg_replace('/[\r\n]+/', '', $this->getQuery());
    }

    /**
     * Returns the result
     *
     * @return Nosto_Result_Graphql_ResultSet
     * @throws Nosto_Request_Http_Exception_AbstractHttpException
     * @throws Nosto_NostoException
     * @throws Nosto_Request_Http_Exception_HttpResponseException
     */
    public function execute()
    {
        $request = $this->initGraphqlRequest();
        $response = $request->postRaw(
            $this->buildPayload()
        );
        if ($response->getCode() !== 200) {
            throw Nosto_Exception_Builder::fromHttpRequestAndResponse($request, $response);
        }

        return Nosto_Result_Graphql_ResultSetBuilder::fromHttpResponse($response);
    }

    /**
     * Returns if recos should use preview mode. You can set asString to
     * true and when the method returns true or false as a string. This is
     * needed for constructing the query.
     *
     * @param bool $asString
     * @return bool|string
     */
    public function isPreviewMode($asString = false)
    {
        if ($asString) {
            return $this->previewMode ? 'true' : 'false';
        }
        return $this->previewMode;
    }

    /**
     * @param bool $previewMode
     */
    public function setPreviewMode($previewMode)
    {
        $this->previewMode = $previewMode;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
}
