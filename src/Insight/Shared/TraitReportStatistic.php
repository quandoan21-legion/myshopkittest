<?php


namespace MyShopKit\Insight\Shared;


use Exception;
use MyShopKit\Insight\Shared\Query\QueryBuilder;

trait TraitReportStatistic
{
    public function getReport(string $queryFilter, array $aAdditionalData = [], string $postID = ''): array
    {
        $responseClassName = $this->generateResponseClass($queryFilter);
        $queryClassName = $this->generateQueryClass($queryFilter);
        if (class_exists($queryClassName) && class_exists($responseClassName)) {
            $oResponse = new $responseClassName;
            $oResponse->setAdditional($aAdditionalData);

            /**
             * @var $oQueryClass QueryBuilder
             */
            $oQueryClass = (new $queryClassName);
            $aData = $oQueryClass->setTable($this->getTable())
                ->setAdditional($aAdditionalData)
                ->setPostID($postID)
                ->select()
                ->setPostType($this->getPostType())
                ->query($oResponse);
        } else {
            throw new Exception(esc_html__('Oops! this filter does not support by us', 'myshopkit'));
        }

        return $aData;
    }

}