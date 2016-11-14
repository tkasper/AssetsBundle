<?php

namespace Becklyn\AssetsBundle\Twig\Extension\Node;

use Becklyn\AssetsBundle\Cache\AssetCache;
use Twig_Compiler;
use Twig_Node;
use Twig_Node_Expression_Binary_Concat;
use Twig_Node_Expression_Constant;
use Twig_Node_Spaceless;


abstract class AssetsNode extends Twig_Node
{
    /**
     * @param string[] $files the file definitions
     * @param int      $lineNo
     * @param string   $tag
     */
    public function __construct (array $files, int $lineNo, string $tag)
    {
        parent::__construct(
            [
                'files' => new Twig_Node($files),
            ],
            [],
            $lineNo,
            $tag
        );
    }


    /**
     * {@inheritdoc}
     */
    public function compile (Twig_Compiler $compiler)
    {
        // Add debug info which will generate some comments in the translated Twig file which maps the template's line number to the code's line number
        $compiler
            ->addDebugInfo($this);

        // if there are no files given - just compile to nothing
        if (empty($this->getNode('files')))
        {
            return;
        }

        // TODO: This section needs to be reworked again to emit the node's content for each asset reference

        // Generate the unique identifier for this node's asset references
        $identifier = sha1(implode(':', $this->getAssetReferences()));
        // Lookup in the cache (e.g. the Assets Cache Table) whether this node has been cached...
        $assetUrl = $this->assetCache->get($identifier);

        // ..if it hasn't we simply ignore/skip it to not cause any further performance impacts
        // by dynamically resolving it at runtime
        if (is_null($assetUrl))
        {
            return;
        }

        // Finally let Twig know about our asset's URL so it can map {{ asset_url }} from within the node to the correct asset

        $compiler
            ->write('$context[')
            // Retrieve the mapped variable: becklyn_assets_url ==> asset_url
            ->repr($this->getAttribute("becklyn_asset_url"))
            // Use the same implementation as {{ asset() }} does to resolve the correct asset URL at get_magic_quotes_runtime()
            // as we don't have any clean way to retrieve the app's URL when executing from the CLI
            ->raw("] = \$this->env->getExtension('assets')->getAssetUrl(")
            ->repr($assetUrl)
            ->raw(");\n")

            // compile body
            ->subcompile($this->getNode("body"))

            // don't leak the variable in other regions
            ->write('unset($context[')
            ->repr($this->getAttribute("becklyn_asset_url"))
            ->raw("]);\n");

        // The resulting code should look like this:

        /**
         $context['asset_url'] = $this->env->getExtension('assets')->getAssetUrl('assets/js/1237634u7wegrweßr.js');

         <<some voodoo to properly escape the node's body template (e.g. script or style tag) and obviously inserting our {{ asset_url }} value>>

         unset($context["asset_url"]);
         */
    }


    /**
     * Parses the 'files' attribute and returns all values as string
     *
     * @return string[]
     */
    public function getAssetReferences ()
    {
        $result = [];

        foreach ($this->getNode('files') as $file)
        {
            $result[] = $this->getNodePath($file);
        }

        return $result;
    }


    /**
     * Extracts the path for the given Twig Node and tries to resolve the asset path
     *
     * @param Twig_Node $node
     *
     * @return array
     */
    private function getNodePath (Twig_Node $node)
    {
        $assetPath = null;

        if ($node instanceof Twig_Node_Expression_Constant)
        {
            $assetPath = $node->getAttribute('value');
        }
        else if ($node instanceof Twig_Node_Expression_Binary_Concat)
        {
            $assetPath =  $this->getNodePath($node->getNode('left')) . $this->getNodePath($node->getNode('right'));
        }

        return $assetPath;
    }



    /**
     * Returns the asset type
     *
     * @return string
     */
    abstract public function getAssetType () : string;
}
