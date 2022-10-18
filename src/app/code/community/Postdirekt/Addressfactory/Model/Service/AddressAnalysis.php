<?php

/**
 * See LICENSE.md for license details.
 */

declare(strict_types=1);

use Mage_Sales_Model_Order_Address as OrderAddress;
use PostDirekt\Sdk\AddressfactoryDirect\Exception\AuthenticationException;
use PostDirekt\Sdk\AddressfactoryDirect\Exception\ServiceException;
use PostDirekt\Sdk\AddressfactoryDirect\Model\RequestType\InRecordWSType;
use PostDirekt\Sdk\AddressfactoryDirect\RequestBuilder\RequestBuilder;
use PostDirekt\Sdk\AddressfactoryDirect\Service\ServiceFactory;
use Postdirekt_Addressfactory_Helper_Data as Helper;
use Postdirekt_Addressfactory_Model_Analysis_ResponseMapper as ResponseMapper;
use Postdirekt_Addressfactory_Model_Analysis_Result as AnalysisResult;
use Postdirekt_Addressfactory_Model_Config as Config;
use Postdirekt_Addressfactory_Model_Logger as Logger;
use Postdirekt_Addressfactory_Model_Resource_Analysis_Result_Collection as AnalysisResultCollection;

class Postdirekt_Addressfactory_Model_Service_AddressAnalysis
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ResponseMapper
     */
    private $recordResponseMapper;

    /**
     * @var Helper
     */
    private $dataHelper;

    /**
     * @var AnalysisResultCollection
     */
    private $analysisResultCollection;

    public function __construct()
    {
        $this->config = Mage::getSingleton('postdirekt_addressfactory/config');
        $this->serviceFactory = new ServiceFactory();
        $this->requestBuilder = new RequestBuilder();
        $this->logger = Mage::getModel('postdirekt_addressfactory/logger');
        $this->recordResponseMapper = Mage::getModel('postdirekt_addressfactory/analysis_responseMapper');
        $this->dataHelper = Mage::helper('postdirekt_addressfactory/data');
        $this->analysisResultCollection = Mage::getModel('postdirekt_addressfactory/analysis_result')->getCollection();
    }

    public function analyse(array $addresses): array
    {
        $addressIds = [];
        foreach ($addresses as $address) {
            $addressIds[] = $address->getEntityId();
        }

        $analysisResults = $this->analysisResultCollection
            ->addFieldToFilter(AnalysisResult::ORDER_ADDRESS_ID, ['in' => $addressIds])
            ->getItems();

        /** @var InRecordWSType[] $recordRequests */
        $recordRequests = array_reduce(
            $addresses,
            function (array $recordRequests, OrderAddress $orderAddress) use ($analysisResults) {
                if (!array_key_exists($orderAddress->getEntityId(), $analysisResults)) {
                    $recordRequests[] = $this->buildRequest($orderAddress);
                }

                return $recordRequests;
            },
            []
        );

        if (empty($recordRequests)) {
            return $analysisResults;
        }

        try {
            $service = $this->serviceFactory->createAddressVerificationService(
                $this->config->getApiUser(),
                $this->config->getApiPassword(),
                $this->logger
            );
            $records = $service->getRecords(
                $recordRequests,
                null,
                $this->config->getConfigurationName(),
                $this->config->getMandateName()
            );
            $newAnalysisResults = $this->recordResponseMapper->mapRecordsResponse($records);

            foreach ($newAnalysisResults as $analysisResult) {
                $this->analysisResultCollection->addItem($analysisResult);
            }

            $this->analysisResultCollection->save();
        } catch (AuthenticationException $exception) {
            throw new \RuntimeException(
                $this->dataHelper->__('Authentication error.', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        } catch (ServiceException $exception) {
            throw new \RuntimeException(
                $this->dataHelper->__('Service exception: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        } catch (Exception $exception) {
            throw new \RuntimeException(
                $this->dataHelper->__('Could not save analysis result.'),
                $exception->getCode(),
                $exception
            );
        }

        // add new records to previously analysis results from db, do a union on purpose to keep keys
        return $newAnalysisResults + $analysisResults;
    }


    /**
     * @param Mage_Sales_Model_Order_Address $address
     * @return InRecordWSType
     */
    private function buildRequest(OrderAddress $address): InRecordWSType
    {
        $this->requestBuilder->setMetadata((int)$address->getEntityId());
        $this->requestBuilder->setAddress(
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getCity(),
            implode(' ', $address->getStreet()),
            ''
        );
        $this->requestBuilder->setPerson(
            $address->getFirstname(),
            $address->getLastname()
        );

        return $this->requestBuilder->create();
    }
}
