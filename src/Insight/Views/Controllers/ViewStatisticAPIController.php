<?php


namespace MyShopKit\Insight\Views\Controllers;


use Exception;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Insight\Interfaces\IInsightController;
use MyShopKit\Insight\Shared\TraitReportStatistic;
use MyShopKit\Insight\Views\Database\ViewStatisticTbl;
use MyShopKit\Insight\Views\Models\ViewStatisticModel;
use MyShopKit\Insight\Shared\TraitUpdateDeleteCreateInsightValidation;
use MyShopKit\Shared\AutoPrefix;
use WP_REST_Request;
use WP_REST_Response;

class ViewStatisticAPIController implements IInsightController
{
    use TraitUpdateDeleteCreateInsightValidation;
    use TraitReportStatistic;

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
    }

    public function registerRouters()
    {
        register_rest_route(MYSHOPKIT_REST, 'insights/popups/views',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportViews'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );

        register_rest_route(MYSHOPKIT_REST, 'insights/popups/views/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'reportViewBySpecifyingPostID'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'PUT',
                    'callback'            => [$this, 'updateView'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'DELETE',
                    'callback'            => [$this, 'deleteView'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function deleteView(WP_REST_Request $oRequest)
    {
        $aValidation = $this->validateCreateOrUpdateInsight($oRequest->get_param('id'), true,
            AutoPrefix::namePrefix('popup'));

        if (($aValidation['status'] == 'success')) {
            $id = ViewStatisticModel::delete($aValidation['data']['postID'], $aValidation['data']['shopID']);
            if (!empty($id)) {
                return MessageFactory::factory('rest')
                    ->success(esc_html__('The view has deleted in database successfully',
                        'myshopkit'));
            } else {
                return MessageFactory::factory('rest')
                    ->error(esc_html__('We could not deleted the view in database',
                        'myshopkit'), 401);
            }
        } else {
            return MessageFactory::factory('rest')->error($aValidation['message'], $aValidation['code']);
        }
    }

    public function reportViews(WP_REST_Request $oRequest)
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
                        'type'     => 'view',
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

    public function reportViewBySpecifyingPostID(WP_REST_Request $oRequest)
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
                        'type'     => 'view',
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
    public function updateView(WP_REST_Request $oRequest): WP_REST_Response
    {
        $aValidationResponse = $this->validateCreateOrUpdateInsight($oRequest->get_param('id'), false,
            AutoPrefix::namePrefix('popup'));

        if (($aValidationResponse['status'] == 'success')) {
            $aResult = $this->updateViewedToday(
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
                            'type'     => 'view',
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
     * Lưu lại số lượt view trong ngày hôm nay. Nếu đã có view rồi thì tự động tăng thêm 1
     *
     * @param $postID
     * @param $shopID
     *
     * @return array
     */
    public function updateViewedToday($postID, $shopID): array
    {
        if (ViewStatisticModel::isViewedToday($shopID, $postID)) {
            $viewID = ViewStatisticModel::getIDWithPostIDAndShopID($shopID, $postID);
            $total = (int)ViewStatisticModel::getField($viewID, 'total') + 1;
            $status = ViewStatisticModel::update([
                'total' => $total,
                'ID'    => $viewID
            ]);

            if (!empty($status)) {
                return MessageFactory::factory()
                    ->success(
                        esc_html__('The view has updated in database successfully', 'myshopkit'),
                        [
                            'id' => (string)$viewID
                        ]
                    );
            } else {
                return MessageFactory::factory()->error(
                    esc_html__('We could not updated the view', 'myshopkit'),
                    401
                );
            }
        } else {
            $status = ViewStatisticModel::insert([
                'shopID' => $shopID,
                'postID' => $postID
            ]);

            if (!empty($status)) {
                return MessageFactory::factory()->success(
                    esc_html__('The view has updated in database successfully', 'myshopkit'),
                    [
                        'id' => (string)$status
                    ]
                );
            } else {
                return MessageFactory::factory()->error(esc_html__('We could not updated the view', 'myshopkit'),
                    401);
            }
        }
    }

    public function getTable(): string
    {
        return ViewStatisticTbl::getTblName();
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
