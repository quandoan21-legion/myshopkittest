<?php

namespace MyShopKit\Insight\Interfaces;

interface IInsightController
{
    public function getTable(): string;

    public function getPostType(): string;

    public function generateResponseClass(string $queryFilter): string;

    public function generateQueryClass(string $queryFilter): string;

    public function getReport(string $queryFilter, array $aAdditionalData = [], string $postID = ''): array;
}
