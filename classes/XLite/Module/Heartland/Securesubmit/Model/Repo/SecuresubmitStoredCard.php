<?php

namespace XLite\Module\Heartland\Securesubmit\Model\Repo;

class SecuresubmitStoredCard extends \XLite\Model\Repo\ARepo
{
    protected function getIdField()
    {
        return 'id';
    }

    public function search(\XLite\Core\CommonCell $cnd)
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $this->currentSearchCnd = $cnd;

        foreach ($this->currentSearchCnd as $key => $value) {
            $this->callSearchConditionHeader($key, $value, $queryBuilder);
        }

        return $this->searchResult($queryBuilder);
    }

    public function searchResult(\Doctrine\ORM\QueryBuilder $qb)
    {
        return $qb->getResult();
    }

    protected function callSearchConditionHeader($key, $value, $queryBuilder)
    {
        if ($this->isSearchParamHasHandler($key)) {
            $this->{'prepareCnd' . ucfirst($key)}($queryBuilder, $value);
        }
    }

    protected function isSearchParamHasHandler($param)
    {
        return in_array($param, $this->getHandlingSearchParams());
    }

    protected function getHandlingSearchParams()
    {
        return array();
    }
}