<?php
namespace Phifty\TwigExtensions\ReactDirective;
use Twig_TokenParser;
use Twig_Token;
use Twig_Node_Expression_Constant;
use Twig_Node_Expression_Array;
use Twig_Node_Expression_Name;

/**
 * {% reactapp "CRUDHasManyEditor" with { ..... } %}
 */
class ReactDirectiveParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $attributes = [
            'reactapp' => null,
            'bind_to' => null,
            'config' => [],
        ];
        while (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            if ($stream->test(Twig_Token::STRING_TYPE)) {
                $attributes['reactapp'] = $stream->next()->getValue();
            } else {
                throw new Exception('Expecting string as reactapp name');
            }

            if ($stream->test(Twig_Token::NAME_TYPE, 'with')) {
                $stream->next(); // skip 'with'

                $node = $this->parser
                    ->getExpressionParser()
                    ->parseExpression(); // Twig_Node_Expression_Array
                $attributes['config'] = $node;

            } else if ($stream->test(Twig_Token::NAME_TYPE, 'on')) {
                $stream->next(); // skip 'on'
                $token = $stream->expect(Twig_Token::STRING_TYPE);
                $attributes['bind_to']  = $token->getValue();
            }
        }
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        return new ReactAppNode($attributes, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'reactapp';
    }
}

