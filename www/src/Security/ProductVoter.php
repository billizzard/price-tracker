<?php
namespace App\Security;

use App\Entity\Product;
use App\Entity\Watcher;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoter extends Voter
{
    const ADD = 'add';
    const EDIT   = 'edit';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::ADD, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Watcher) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        /** @var Product */
        $product = $subject; // $subject must be a Post instance, thanks to the supports method

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            // if the user is an admin, allow them to create new posts
            case self::ADD:
                if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
                    return true;
                }

                break;

            // if the user is the author of the post, allow them to edit the posts
            case self::EDIT:
                if ($user->getId() === $product->getUser()->getId()) {
                    return true;
                }

                break;
        }

        return false;
    }
}