<?php
/**
* @package     
* @subpackage  
* @author      Brice Tencé
* @copyright   2011 Brice Tencé
* @link        
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* plugin for jResponseHTML, which processes less files
*/

require_once 'lessphp_compiler/lessc.inc.php';

define('LESSPHP_COMPILE_ALWAYS', 1 );
define('LESSPHP_COMPILE_ONCHANGE', 2 ); //default value : lessphp default behaviour
define('LESSPHP_COMPILE_ONCE', 3 );

class lessphpHTMLResponsePlugin implements jIHTMLResponsePlugin {

    protected $response = null;

    public function __construct(jResponse $c) {
        $this->response = $c;
    }

    /**
     * called just before the jResponseBasicHtml::doAfterActions() call
     */
    public function afterAction() {
    }

    /**
     * called when the content is generated, and potentially sent, except
     * the body end tag and the html end tags. This method can output
     * directly some contents.
     */
    public function beforeOutput() {
        if (!($this->response instanceof jResponseHtml))
            return;
        global $gJConfig;

        $compileFlag = LESSPHP_COMPILE_ONCHANGE;
        if( isset($gJConfig->jResponseHtml['lessphp_compile']) ) {
            switch($gJConfig->jResponseHtml['lessphp_compile']) {
            case 'always':
                $compileFlag = LESSPHP_COMPILE_ALWAYS;
                break;
            case 'onchange':
                $compileFlag = LESSPHP_COMPILE_ONCHANGE;
                break;
            case 'once':
                $compileFlag = LESSPHP_COMPILE_ONCE;
                break;
            }
        }

        $inputCSSLinks = $this->response->getCSSLinks();
        $outputCSSLinks = array();

        foreach( $inputCSSLinks as $inputCSSLinkUrl=>$CSSLinkParams ) {
            $CSSLinkUrl = $inputCSSLinkUrl;
            if( isset($CSSLinkParams['rel']) && $CSSLinkParams['rel'] == 'stylesheet/less' ) {
                //we suppose url starts with basepath. Other cases should not have a "'rel' => 'stylesheet/less'" param ...
                if( substr($CSSLinkUrl, 0, strlen($gJConfig->urlengine['basePath'])) != $gJConfig->urlengine['basePath'] ) {
                    throw new Exception("File $CSSLinkUrl seems not to be located in your basePath : it can not be processed with lessphp");
                } else {
                    $filePath = jApp::wwwPath() . substr($CSSLinkUrl, strlen($gJConfig->urlengine['basePath']));

                    $outputSuffix = '';
                    if( substr($filePath, -5) != '.less' ) {
                        //append .less at the end of filename if it is not already the case ...
                        $outputSuffix .= '.less';
                    }
                    $outputSuffix .= '.css';
                    $outputPath = $filePath.$outputSuffix;

                    try {
                        $compile = true;
                        if( is_file($outputPath) ) {
                            if( ($compileFlag == LESSPHP_COMPILE_ALWAYS) ) {
                                unlink($outputPath);
                            } elseif( ($compileFlag == LESSPHP_COMPILE_ONCE) ) {
                                $compile = false;
                            }
                            //LESSPHP_COMPILE_ONCHANGE is lessphp's natural behaviour. So we let him do ...
                        }
                        if( $compile ) {
                            lessc::ccompile($filePath, $outputPath);
                        }
                        $CSSLinkUrl = $CSSLinkUrl . $outputSuffix;
                    } catch (exception $ex) {
                        trigger_error("lessc fatal error on file $filePath:<br />".$ex->getMessage(), E_USER_ERROR);
                    }
                }
                unset($CSSLinkParams['rel']);
            }

            $outputCSSLinks[$CSSLinkUrl] = $CSSLinkParams;
        }

        $this->response->setCSSLinks( $outputCSSLinks );
    }

    /**
     * called just before the output of an error page
     */
    public function atBottom() {
    }

    /**
     * called just before the output of an error page
     */
    public function beforeOutputError() {
    }
}
