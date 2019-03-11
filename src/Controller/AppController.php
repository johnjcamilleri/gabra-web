<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\I18n;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        $this->loadComponent('Search');

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
    }

    private $alphabet = array(
        "a", "b", "ċ", "d", "e", "f", "ġ", "g", "għ", "h", "ħ", "i", "ie", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "ż", "z");
    private $consonants = array(
        "b", "ċ", "d", "f", "ġ", "g", "għ", "h", "ħ", "j", "k",
        "l", "m", "n", "p", "q", "r", "s", "t", "v", "w", "x", "ż", "z");
    private $parts_of_speech;
    private $root_types;

    private function enums() {
        $this->parts_of_speech = array(
            // Universal POS tags
            // http://universaldependencies.github.io/docs/u/pos/index.html
            'ADJ'   => __('adjective'),
            'ADP'   => __('adposition'),
            'ADV'   => __('adverb'),
            'AUX'   => __('auxiliary verb'),
            'CONJ'  => __('coordinating conjunction'),
            'DET'   => __('determiner'),
            'INTJ'  => __('interjection'),
            'NOUN'  => __('noun'),
            'NUM'   => __('numeral'),
            'PART'  => __('particle'),
            'PRON'  => __('pronoun'),
            'PROPN' => __('proper noun'),
            'PUNCT' => __('punctuation'),
            'SCONJ' => __('subordinating conjunction'),
            'SYM'   => __('symbol'),
            'VERB'  => __('verb'),
            'X'     => __('other'),
        );
        $this->root_types = array(
            'strong'       => __('strong'),
            'geminated'    => __('geminated'),
            'weak-initial' => __('weak initial'),
            'weak-medial'  => __('weak medial'),
            'weak-final'   => __('weak final'),
            'irregular'    => __('irregular'),
        );
    }

    public function beforeFilter(Event $event) {

        // If site maintenance, then just die
        if (Configure::read('Site.status') === 0) {
            header('HTTP/1.0 503 Service Unavailable');
            header('Content-Type: text/html; charset=utf-8');
            die("Ġabra is down for maintenance, please try again later.");
        }

        // Language
        if ($this->request->getQuery('lang')) {
            // Changing language via query string
            $this->request->getSession()->write('Config.language', $this->request->getQuery('lang'));
        }
        if (!$this->request->getSession()->read('Config.language')) {
            // No language set, resort to default
            $this->request->getSession()->write('Config.language', 'en'); // default is English
        }
        // Read current language from session
        $language = $this->request->getSession()->read('Config.language');
        I18n::setLocale($language); // for gettext
        $this->set('language',  $language); // for templates

        // Localised web content
        $this->loadModel('Messages');
        $this->set('content', $this->Messages->webContent($language));

        // General stuff
        $this->enums();
        $common = array(
            'alphabet' => $this->alphabet,
            'consonants' => $this->consonants,
            'root_types' => $this->root_types,
            'parts_of_speech' => $this->parts_of_speech
        );
        $this->set('common', $common);
    }

    /* Wrappers over Session->setFlash */
    public function setMessageGood($message) {
        return $this->setMessage($message, 'good');
    }
    public function setMessageBad($message) {
        return $this->setMessage($message, 'bad');
    }
    public function setMessageInfo($message) {
        return $this->setMessage($message, 'info');
    }
    private function setMessage($message, $class) {
        return $this->Flash->set($message, [ 'params' => [ 'class' => $class ] ]);
    }

}
