<?php


namespace MyShopKit\Insight\Clicks\Controllers;


use Exception;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Insight\Clicks\Database\ClickStatisticTbl;
use MyShopKit\Insight\Clicks\Models\ClickStatisticModel;
use MyShopKit\Insight\Interfaces\IInsightController;
use MyShopKit\Insight\Shared\TraitCheckCustomDateInMonth;
use MyShopKit\Insight\Shared\TraitReportStatistic;
use MyShopKit\Insight\Shared\TraitUpdateDeleteCreateInsightValidation;
use MyShopKit\Shared\AutoPrefix;
use WP_REST_Request;
use WP_REST_Response;

class ClickStatisticAPIController implements IInsightController
{
    use TraitUpdateDeleteCreateInsightValidation;
    use TraitCheckCustomDateInMonth;
    use TraitReportStatistic;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
    }

    public function registerRouters()
    {
        register_rest_route(MYSHOPKIT_REST, 'insights/popups/clicks',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportClicks'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(MYSHOPKIT_REST, 'insights/popups/clicks/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportClickBySpecifyingPostID'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'PUT',
                    'callback'            => [$this, 'updateClick'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deleteClick'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function deleteClick(WP_REST_Request $oRequest)
    {
        $aValidation = $this->validateCreateOrUpdateInsight($oRequest->get_param('id'), true,
            AutoPrefix::namePrefix('popup'));

        if (($aValidation['status'] == 'success')) {
            $id = ClickStatisticModel::delete($aValidation['data']['postID'], $aValidation['data']['shopID']);
            if (!empty($id)) {
                return MessageFactory::factory('rest')
                    ->success(esc_html__('The clicks has deleted in database successfully',
                        'myshopkit'));
            } else {
                return MessageFactory::factory('rest')
                    ->error(esc_html__('We could not deleted the clicks in database',
                        'myshopkit'), 401);
            }
        } else {
            return MessageFactory::factory('rest')->error($aValidation['message'], $aValidation['code']);
        }
    }

    public function reportClicks(WP_REST_Request $oRequest)
    {
        if (ebaseGetCurrentShopID(true)) {
            $aAdditional = [
                'from' => $oRequest->get_param('from'),
                'to'   => $oRequest->get_param('to')
            ];
            $filter = $oRequest->get_param('filter');
            $filter = empty($filter) ? 'today' : $filter;

            try {
                if ($filter == 'custom') {
                    if (empty($aAdditional['from'])) {
                        throw new Exception(esc_html__('The param for handling filter custom start  is not null',
                            'myshopkit'));
                    }
                    if (empty($aAdditional['to'])) {
                        throw new Exception(esc_html__('The param for handling filter custom end  is not null',
                            'myshopkit'));
                    }
                }
                $aData = $this->getReport($filter, $aAdditional);

                return MessageFactory::factory('rest')->success(
                    'success',
                    [
                        'type'     => 'click',
                        'summary'  => $aData['summary'],
                        'timeline' => $aData['timeline']
                    ]
                );
            } catch (Exception $exception) {
                return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
            }
        } else {
            return MessageFactory::factory('rest')->error(esc_html__('Sorry, We could not find your shop.',
                'myshopkit'), 404);
        }

    }

    public function reportClickBySpecifyingPostID(WP_REST_Request $oRequest)
    {
        $aValidation = $this->validateCreateOrUpdateInsight($oRequest->get_param('id'), true,
            AutoPrefix::namePrefix('popup'));

        if ($aValidation['status'] === 'success') {
            $popupID = $aValidation['data']['postID'];
            $aAdditional = [
                'from' => $oRequest->get_param('from'),
                'to'   => $oRequest->get_param('to')
            ];
            $filter = $oRequest->get_param('filter');
            $filter = empty($filter) ? 'today' : $filter;

            try {
                if ($filter == 'custom') {
                    if (empty($aAdditional['from'])) {
                        throw new Exception(esc_html__('The param for handling filter custom start  is not null',
                            'myshopkit'));
                    }
                    if (empty($aAdditional['to'])) {
                        throw new Exception(esc_html__('The param for handling filter custom end  is not null',
                            'myshopkit'));
                    }
                }
                $aData = $this->getReport($filter, $aAdditional, $popupID);

                return MessageFactory::factory('rest')->success(
                    'success',
                    [
                        'type'     => 'click',
                        'summary'  => $aData['summary'],
                        'timeline' => $aData['timeline']
                    ]
                );
            } catch (Exception $exception) {
                return MessageFactory::factory('rest')->error($exception->getMessage(), $exception->getCode());
            }
        } else {
            return MessageFactory::factory('rest')->error(
                $aValidation['message'],
                $aValidation['code']
            );
        }

    }

    /**
     * id la popup id
     *
     * @param WP_REST_Request $oRequest
     *
     * @return WP_REST_Response
     * @throws Exception
     */
    public function updateClick(WP_REST_Request $oRequest): WP_REST_Response
    {
        $aValidationResponse = $this->validateCreateOrUpdateInsight($oRequest->get_param('id'), false,
            AutoPrefix::namePrefix('popup'));

        if (($aValidationResponse['status'] == 'success')) {
            $aResult = $this->updateClickedToday(
                $aValidationResponse['data']['postID'],
                $aValidationResponse['data']['shopID']
            );

            if ($aResult['status'] == 'success') {
                $aData = $this->getReport('today', [], $aValidationResponse['data']['postID']);
                return MessageFactory::factory('rest')
                    ->success(
                        $aResult['message'],
                        [
                            'id'       => $aResult['data']['id'],
                            'type'     => 'click',
                            'summary'  => $aData['summary'],
                            'timeline' => $aData['timeline']
                        ]
                    );
            } else {
                return MessageFactory::factory('rest')
                    ->error($aResult['message'], $aResult['code']);
            }
        } else {
            return MessageFactory::factory('rest')
                ->error($aValidationResponse['message'], $aValidationResponse['code']);
        }
    }

    /**
     * Lưu lại số lượt click trong ngày hôm nay. Nếu đã có click rồi thì tự động tăng thêm 1
     *
     * @param $postID
     * @param $shopID
     *
     * @return array
     */
    public function updateClickedToday($postID, $shopID): array
    {
        if (ClickStatisticModel::isClickedToday($shopID, $postID)) {
            $clickID = ClickStatisticModel::getIDWithPostIDAndShopID($shopID, $postID);
            $total = (int)ClickStatisticModel::getField($clickID, 'total') + 1;
            $status = ClickStatisticModel::update([
                'total' => $total,
                'ID'    => $clickID
            ]);

            if (!empty($status)) {
                return MessageFactory::factory()
                    ->success(
                        esc_html__('The clicks has updated in database successfully', 'myshopkit'),
                        [
                            'id' => $clickID
                        ]
                    );
            } else {
                return MessageFactory::factory()->error(
                    esc_html__('We could not updated the click', 'myshopkit'),
                    401
                );
            }
        } else {
            $status = ClickStatisticModel::insert([
                'shopID' => $shopID,
                'postID' => $postID
            ]);

            if (!empty($status)) {
                return MessageFactory::factory()->success(
                    esc_html__('The clicks has updated in database successfully', 'myshopkit'),
                    [
                        'id' => (string)$status
                    ]
                );
            } else {
                return MessageFactory::factory()->error(esc_html__('We could not updated the click', 'myshopkit'),
                    401);
            }
        }
    }

    public function getTable(): string
    {
        return ClickStatisticTbl::getTblName();
    }

    public function getPostType(): string
    {
        return AutoPrefix::namePrefix('popup');
    }

    public function generateResponseClass(string $queryFilter): string
    {
        $ucFirstFilter = ucfirst($queryFilter);
        return "MyShopKit\Insight\Shared\\$ucFirstFilter\\$ucFirstFilter" . "Response";
    }

    public function generateQueryClass(string $queryFilter): string
    {
        $ucFirstFilter = ucfirst($queryFilter);
        return "MyShopKit\Insight\Shared\\$ucFirstFilter\\$ucFirstFilter" . "Query";
    }
}
