<?php

namespace BoxUK\Routing\Output;

use BoxUK\Routing\Rewriter;
use BoxUK\Routing\Input\Request;

/**
 * This filter is meant to be used in conjunction with the RequestRouter
 * request router to re-write urls it can process.
 *
 * @ScopeSingleton(implements="BoxUK\Routing\Output\Filter")
 *
 * @copyright Copyright (c) 2010, Box UK
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://github.com/boxuk/boxuk-routing
 * @since 1.0
 */
class StandardFilter implements Filter {

    /**
     * @var Rewriter
     */
    private $rewriter;

    /**
     * @var string Name of front controller script
     */
    private $name;

    /**
     * Creates a new filter
     * 
     */
    public function __construct( Rewriter $rewriter ) {

        $this->rewriter = $rewriter;
        $this->name = 'server';
        
    }

    /**
     * Sets the name of the php script that acts as the front controller
     *
     * @param string $name eg. 'server.php'
     */
    public function setFrontController( $name ) {

        $this->name = $name;
        
    }

    /**
     * Looks for links in the markup that can be re-written to route-style urls
     * 
     * @param string $html
     */
    public function process( &$html ) {
        
        //TODO: Find a better way to do this
        $currentBacktraceLimit = ini_get( 'pcre.backtrack_limit' );
        if ( $currentBacktraceLimit < strlen($html) ){
            ini_set( 'pcre.backtrack_limit', strlen($html) );
        }

        // attributes

        $html = preg_replace_callback(
            '/(href|value)="([^"]*' . $this->name . '[^"]*)"/i',
            array( $this, 'replaceAttribute' ),
            $html
        );

        // elements

        $elements = array( 'link', 'guid' );

        foreach ( $elements as $element ) {
            $html = preg_replace_callback(
                sprintf( '#(<%1$s>)(' . $this->name . '.*?)(</%1$s>)#i', $element ),
                array( $this, 'replaceElement' ),
                $html
            );
        }

        // forms
        
        $html = preg_replace_callback(
            '#<form\b[^>]*>(.*?)</form>#ms',
            array( $this, 'replaceForm' ),
            $html
        );

        ini_set( 'pcre.backtrack_limit', $currentBacktraceLimit );

    }

    /**
     * Looks through a form to try and use hidden inputs to rewrite the form action
     * 
     * @return string
     */
    public function replaceForm( array $matches ) {
        
        list( $form ) = $matches;

        $inputs = $this->getHiddenInputs( $form );
        $url = $this->getUrlFromInputs( $inputs );
        
        $rewriteInfo = $this->rewriter->getRewriteInfo( $url );
        
        if ( $rewriteInfo != null ) {

            list( $domain, $webRoot, $url, $queryString, $specification ) = $rewriteInfo;
            
            $form = preg_replace(
                '/action="[^#].*?"/',
                sprintf( 'action="%s%s"', $webRoot, $url ),
                $form
            );
            
            preg_match_all( '/(\?|&)(.*?)=/', $queryString, $queryMatches );

            foreach ( $inputs as $name => $aInput ) {
                if ( !in_array(urlencode($name),$queryMatches[2]) ) {
                    $form = str_replace( $aInput['html'], '', $form );
                }
            }

            if ( $specification->getMethod() ) {
                $form = str_replace(
                    '</form>',
                    sprintf(
                        '<input type="hidden" name="__method" value="%s" /></form>',
                        $specification->getMethod()
                    ),
                    $form
                );
            }
            
        }

        return $form;

    }

    /**
     * Returns a URL built from the output of getHiddenInputs()
     *
     * @return string
     */
    protected function getUrlFromInputs( array $inputs ) {

        $queryString = '';
        
        foreach ( $inputs as $name => $input ) {
            $queryString .= sprintf(
                '%s%s=%s',
                $queryString ? '&' : '?',
                urlencode( $name ),
                urlencode( $input['value'] )
            );
        }
        
        return $this->name . '.php' . $queryString;

    }

    /**
     * Extracts all hidden inputs from the form and returns them in the form...
     *
     * array(
     *     'name' => array(
     *         'html' => '<input ... />',
     *         'value' => 'bar'
     *     ),
     *     etc...
     * )
     *
     * @param string $form
     * 
     * @return array
     */
    protected function getHiddenInputs( $form ) {

        $inputs = array();

        if ( preg_match_all( '#<input[^>]*?type="hidden"[^>]*?/>#', $form, $inputMatches ) ) {

            foreach ( $inputMatches[0] as $input ) {

                preg_match( '/name="(.*?)"/', $input, $nameMatches );
                preg_match( '/value="(.*?)"/', $input, $valueMatches );

                if ( !empty($nameMatches) && !empty($valueMatches) ) {

                    list( $IGNORE, $name ) = $nameMatches;
                    list( $IGNORE, $value ) = $valueMatches;

                    $inputs[ $name ] = array(
                        'html' => $input,
                        'value' => $value
                    );

                }

            }

        }

        return $inputs;

    }

    /**
     * Checks the defined route specs to see if this url can be re-written
     * to match one of them.
     * 
     * @return string
     */
    public function replaceAttribute( array $matches ) {

        list( $default, $attribute, $url ) = $matches;

        $newUrl = $this->rewriter->rewrite( $url );

        return $newUrl != $url
            ? sprintf( '%s="%s"', $attribute, $newUrl )
            : $default;

    }

    /**
     * Replaces route urls in elements (eg. <elem>url</elem>)
     * 
     * @return string
     */
    public function replaceElement( array $matches ) {

        list( $default, $startTag, $url, $endTag ) = $matches;

        return sprintf(
            '%s%s%s',
            $startTag,
            $this->rewriter->rewrite( $url, $includeDomain = true ),
            $endTag
        );

    }

}
