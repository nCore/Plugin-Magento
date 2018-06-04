<?php
namespace Synerise;

class SyneriseClient extends SyneriseAbstractHttpClient
{

    protected $_cache = array();


    public function getClientByCustomIdentify($numberCard) {
        return $this->getClientByParameter(array('customIdentify' => $numberCard));
    }

    public function getClientByParameter(array $fields)
    {
        try {
            $response = $this->get(SyneriseAbstractHttpClient::BASE_API_URL . '/client/?' . http_build_query($fields));

            $responseArray = json_decode($response->getBody(), true);
            return isset($responseArray['data']) ? $responseArray['data'] : null;

        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }
    }

    public function getLoyaltyEngagement($search,$locationIdentity = null) {
        try {
            $fields['search'] = (string) $search;
            if($locationIdentity) {
                $fields['locationIdentity'] = (string) $locationIdentity;
            }
            $response = $this->get(SyneriseAbstractHttpClient::BASE_API_URL . '/client/loyaltyengagement?' . http_build_query($fields));

            $responseArray = json_decode($response->getBody(), true);
            return isset($responseArray['data']) ? $responseArray['data'] : null;

        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }
    }

    public function addOrUpdateClient(array $data)
    {
        try {
            $response = $this->post(SyneriseAbstractHttpClient::BASE_API_URL . '/client/', array(
                'json' => $data
            ));

            if ($response->getStatusCode() != '200') {
                throw new Exception\SyneriseException('API Synerise not responsed 200.', 500);
            }

            $responseArray = json_decode($response->getBody(), true);
            return isset($responseArray['data']) ? $responseArray['data'] : null;

        } catch (\Exception $e) {
            if($this->getLogger()) {
                $this->getLogger()->alert($e->getMessage());
            }
            throw $e;
        }

        return false;
    }

    public function batchAddOrUpdateClients(array $items)
    {
        $data = array();
        foreach($items as $item) {
            if(isset($item['email']) && isset($item['data'])) {
                $data[$item['email']] = $item['data'];
            }
        }

        if(!empty($data)) {
            try {
                $response = $this->post(SyneriseAbstractHttpClient::BASE_API_URL . '/client/batch', array(
                    'json' => array('items' => $data)
                ));

                if ($response->getStatusCode() != '200') {
                    throw new Exception\SyneriseException('API Synerise not responsed 200.', 500);
                }

                $responseArray = json_decode($response->getBody(), true);
                return isset($responseArray['data']) ? $responseArray['data'] : null;

            } catch (\Exception $e) {
                if($this->getLogger()) {
                    $this->getLogger()->alert($e->getMessage());
                }
                throw $e;
            }
        }

        return false;
    }

}