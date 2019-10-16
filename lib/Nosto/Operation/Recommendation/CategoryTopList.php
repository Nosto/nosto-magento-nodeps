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
 * Operation class for getting product ids in a category
 */
class Nosto_Operation_Recommendation_CategoryTopList extends Nosto_Operation_Recommendation_AbstractTopList
{
    private $category;

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        $query
            = <<<QUERY
        {
            "query": "mutation(
                    \$customerId: String!,
                    \$category: String!,
                    \$limit: Int!,
                    \$preview: Boolean!,
                    \$hours: Int!
            ) { 
                updateSession(by: BY_CID, id: \$customerId, params: {
                    event: { 
                        type: VIEWED_CATEGORY
                        target: \$category
                    }     
                }) {
                    recos (preview: \$preview, image: VERSION_8_400_400) {
                        category_ids: toplist(
                            hours: \$hours,
                            sort: %s
                            params: {
                                minProducts: 1,
                                maxProducts: \$limit,
                                include: {
                                    categories: [\$category]
                                }
                            }
                        ) {
                            %s {
                                productId
                            }     
                        }
                    }
                }
            }",
            "variables": {
                "customerId": "%s",
                "category": "%s", 
                "limit": "%d",
                "preview": %s,
                "hours": "%d"
            }
        }
QUERY;
        $formatted = sprintf(
            $query,
            $this->getSort(),
            self::GRAPHQL_DATA_KEY,
            $this->getCustomerId(),
            $this->category,
            $this->getLimit(),
            $this->isPreviewMode(true),
            $this->getHours()
        );

        return $formatted;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}
