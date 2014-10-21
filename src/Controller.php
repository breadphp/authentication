<?php
namespace Bread\Authentication;

use Bread\Networking\HTTP\Client\Exceptions\NotFound;
use Bread\Networking\HTTP\Response;
use Bread\Promises\When;
use Bread\REST;
use Bread\REST\Behaviors\ARO\Authenticated;
use Bread\REST\Components\Authentication\Token\Model as Token;
use Bread\Types\DateTime;

class Controller extends REST\Controller
{

    public function getMe()
    {
        if ($this->aro instanceof Authenticated) {
            if (isset($this->response->headers['X-Token'])) {
                return $this->get($this->aro, array(), array(
                    'X-Token' => $this->response->headers['X-Token']
                ));
            } else {
                return $this->get($this->aro);
            }
        } else {
            return $this->firewall->authenticate("Authentication");
        }
    }

    public function getLogout()
    {
        $token = $this->request->headers['Authorization'];
        $token = split(" ", $token, 2)[1];
        $this->response->unsetCookie("Authorization");
        return Token::first(array(
            'data' => $token,
            '$or' => array(
                array('expire' => array('$gt' => new DateTime())),
                array('expire' => null)
            )
        ))->then(function ($token) {
            $token->delete();
            $this->response->status(Response::STATUS_NO_CONTENT);
            return $this->response;
        });
    }

    public function getAvatar($resource)
    {
        $domain = $this->request->headers['host'];
        return static::authorize($this->aro, $resource, 'read', $domain)->then(function($resource) {
            if (!$resource->thumbnailPhoto) {
                throw new NotFound($this->request->uri);
            }
            $this->response->type('image/jpeg');
            $this->response->cache('+1 day');
            return $this->response->flush($resource->thumbnailPhoto);
        });
    }

    public function controlledResource(array $parameters = array())
    {
        return When::resolve(array());
    }
}
