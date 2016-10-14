<?php
class Sabai_Addon_Markdown_Parser extends MarkdownExtra_Parser
{
    function _doCodeBlocks_callback($matches)
    {
        $codeblock = $matches[1];
        $codeblock = $this->outdent($codeblock);
        $codeblock = htmlspecialchars($codeblock, ENT_NOQUOTES);

        # trim leading newlines and trailing newlines
        $codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);

        $codeblock = "<pre class=\"prettyprint\"><code>$codeblock\n</code></pre>";
        return "\n\n".$this->hashBlock($codeblock)."\n\n";
    }
    
    function makeCodeSpan($code)
    {
	#
	# Create a code span markup for $code. Called from handleSpanToken.
	#
        $code = htmlspecialchars(trim($code), ENT_NOQUOTES);
        return $this->hashPart('<code class="prettyprint">' . $code . '</code>');
    }
}
