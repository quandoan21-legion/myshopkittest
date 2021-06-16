<?php


namespace MyShopKit\Popup\Services\Post;


use Exception;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Shared\Post\IDeleteUpdateService;
use MyShopKit\Shared\Post\TraitIsPostAuthor;
use WP_Post;

class DeletePostServiceDelete implements IDeleteUpdateService
{
    use TraitIsPostAuthor;

    private $postID;

    public function setID($id): self
    {
        $this->postID = $id;

        return $this;
    }


    public function delete(): array
    {
        try {
            $this->isPostAuthor($this->postID);
            $oPost = wp_delete_post($this->postID, true);

            if ($oPost instanceof WP_Post) {
                return MessageFactory::factory()->success(esc_html__('Congrats, the popup has been deleted.',
                    'myshopkit'), [
                    'id' => (string)$oPost->ID
                ]);
            }

            return MessageFactory::factory()->error(esc_html__('Sorry, We could not delete this popup.',
                'myshopkit'),
                400);
        } catch (Exception $oException) {
            return MessageFactory::factory()->error(
                $oException->getMessage(),
                $oException->getCode()
            );
        }

    }
}
