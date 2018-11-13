<?php

namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\Config;
use GrShareCode\Api\Authorization\ApiKeyAuthorization;
use GrShareCode\Api\Authorization\ApiTypeException;
use GrShareCode\Api\GetresponseApi;
use GrShareCode\Api\GetresponseApiClient;
use GrShareCode\Api\UserAgentHeader;
use GrShareCode\DbRepositoryInterface;

/**
 * Class GetresponseApiClientFactory
 * @package ShareCode
 */
class GetresponseApiClientFactory
{
    /**
     * @param string $apiKey
     * @param string $apiType
     * @param string $domain
     * @param DbRepositoryInterface $sharedCodeRepository
     * @param string $pluginVersion
     * @return GetresponseApiClient
     * @throws ApiTypeException
     * @throws ApiTypeException
     */
    public static function createFromParams($apiKey, $apiType, $domain, $sharedCodeRepository, $pluginVersion)
    {
        return new GetresponseApiClient(
            $getResponseApiClient = new GetresponseApi(
                new ApiKeyAuthorization(
                    $apiKey,
                    $apiType,
                    $domain
                ),
                Config::X_APP_ID,
                new UserAgentHeader(
                    Config::SERVICE_NAME,
                    Config::SERVICE_VERSION,
                    $pluginVersion
                )
            ),
            $sharedCodeRepository
        );
    }
}
