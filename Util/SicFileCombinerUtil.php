<?php


namespace Ling\SicTools\Util;


use Ling\BabyYaml\BabyYamlUtil;
use Ling\Bat\ArrayTool;
use Ling\Bat\BDotTool;
use Ling\DirScanner\YorgDirScannerTool;
use Ling\SicTools\Exception\SicToolsException;

/**
 * The SicFileCombinerUtil class.
 *
 * The idea of a "combiner" is that the configuration array is broken into multiple files.
 * Typically, this is what happens naturally in an environment with plugins: each plugin brings
 * a part of the configuration in the form of one or multiple files; each plugin owns one or more files.
 *
 * Then the role of a "combiner" is to parse all those files and make one united configuration.
 *
 *
 * By default, the files are merged in the order they appear (usually ordered alphabetically), and are merged
 * using the so-called arrayMergeReplaceRecursive algorithm
 *
 * Basically, this algorithm merges arrays together, and when a value already exists, two cases:
 *
 * - either the replaced value is an array, in which case the new value gets appended to that array
 * - or the replaced value is a scalar value (i.e. not an array), in which case the new value completely replaces the old one
 *
 * The exception being you can't override a numerical key (which indicates a numeric array which always calls for
 * a merge operation).
 *
 * See the @ref(ArrayTool::arrayMergeReplaceRecursive) method for more info.
 *
 * Apart from providing that default algorithm, the extra-value brought by this combiner is that it allows syntax additions.
 *
 * In this particular combiner object, the following features are implemented:
 *
 *
 * - lazy override variables
 * - variable references
 *
 *
 * Lazy override variables
 * ============
 *
 * Ams is a variant based upon the arrayMergeReplaceRecursive algorithm; its goal is to address some limitations
 * of the arrayMergeReplaceRecursive algorithm.
 *
 * What are those limitations?
 * The main limitation of the arrayMergeReplaceRecursive algorithm is that arrays are merged in their order
 * of appearance, so that when two arrays are merged, the second array is always pasted on top of the first one,
 * overriding its values.
 *
 * So for instance if file aa.byml contains this:
 *
 * ```yaml
 * my_color: blue
 * ```
 *
 * and file bb.yml contains this:
 *
 * ```yaml
 * my_color: red
 * ```
 *
 *
 * Then the combined file will look like this:
 *
 * ```yaml
 * my_color: red
 * ```
 *
 *
 * That's because b comes after a in the alphabet, and so the bb.yml file will always be merged on top of the
 * aa.byml file.
 *
 *
 * Sometimes though, in particular in a plugin environment where plugins have equal "rights", the plugin aa.byml
 * should have the right to override the configuration of plugin bb.yml, exactly in the same manner as
 * bb.yml having the right to override the configuration of aa.byml.
 *
 * In other words, a plugin shall be able to SUBSCRIBE to another plugin's service (aka configuration),
 * without regards to its alphabetical order.
 *
 *
 * And so that's what the lazy override syntax addresses.
 *
 * The lazy override syntax basically uses the @page(bdot) syntax, but it prefixes it with a
 * symbol (the dollar symbol by default).
 * So for instance, here is the content of an hypothetical z.byml file:
 *
 *
 *
 * ```yaml
 * service_from_Z:
 *      instance: My\Company\Util\ServiceOne
 *      methods:
 *          adopt: []
 *
 * ```
 *
 *
 * And now here is the content of a file aa.byml, who wants to hook into the service from zz.byml:
 *
 *
 *
 * ```yaml
 * $service_from_Z.methods.adopt:
 *      - item_one
 *      - item_two
 * ```
 *
 *
 * In the end, the "compiled" file/array will look like this:
 *
 * ```yaml
 * service_from_Z:
 *      instance: My\Company\Util\ServiceOne
 *      methods:
 *          adopt:
 *              - item_one
 *              - item_two
 *
 * ```
 *
 * So again, the lazy override syntax is just a way for a plugin to override a configuration key from another plugin.
 *
 * A lazy override variable must be declared at the root of a file (i.e. not in a nested key).
 * What's done under the hood is that when a lazy override variable is found, it is temporarily extracted from the
 * combining process, and stored in memory.
 *
 * Then after the final array is combined, the stored variables are injected using the same arrayMergeReplaceRecursive
 * algorithm into the combined array.
 *
 *
 *
 *
 *
 * Variable references
 * ============
 *
 * A variable reference is just a reference to a (previously declared) lazy override variable.
 * *
 * So for instance if my file contains this:
 *
 * ```yaml
 * my_var: 66
 * ```
 *
 * Then I can use the ${tag} notation, for instance:
 *
 *
 * ```yaml
 * my_service:
 *      instance: My\Company\Util\ServiceOne
 *      methods:
 *          makeCoffee:
 *              - arg1
 *              - ${my_var}
 *              - arg3
 * ```
 *
 *
 *
 * Variables must be declared at the root level.
 * Bdot syntax is allowed.
 *
 * The goal of variables is to allow an application maintainer to configure her system with ease.
 * Usually, a plugin author would create an array containing all her variables.
 * That would avoid potential collisions with other plugins variables.
 *
 * Here is a concrete fake example of how the variable system was meant to be used.
 *
 * A plugin author creates her file aa.byml with the following content:
 *
 * ```yaml
 *
 * plugin_a_vars:
 *      color: red
 *
 * my_service_A:
 *      nested:
 *          very_deep:
 *              so_boring:
 *                  to_override:
 *                      -
 *                          instance: paa
 *                          methods:
 *                              doCoffee:
 *                                  - 11
 *                                  - 33
 *                      -
 *                          instance: poo
 *                          methods:
 *                              doTea:
 *                                  arg1: 11
 *                                  color: ${plugin_a_vars.color}
 *                                  arg3: 33
 *      others: blabla
 *
 * my_service_2: etc...
 *
 *
 * ```
 *
 * Then in a separate file, the application maintainer put the following content:
 *
 * ```yaml
 * $plugin_a_vars.color: blue
 * ```
 *
 *
 * So, in this case, what variables permit is:
 *
 * - plugin authors can provide their own variable by declaring them in their configuration file
 * - using variables saves some typing for the app maintainer who can override them from another configuration file (using lazy override technique for instance)
 *
 *
 * If she didn't use the variable system, the app maintainer would had typed this instead:
 *
 *
 * ```yaml
 * $my_service_A.nested.very_deep.so_boring.to_override.1.methods.doTea.color: blue
 * ```
 *
 *
 * Note: plugins technically can also use this lazy override syntax to "use" other plugins.
 * And so it is recommended that the app maintainer creates a file which comes last in the alphabetical order
 * (for instance: zzz.byml), so that the app maintainer configuration prevails no matter what.
 * That's because even the lazy override variables system is ruled by the alphabetical order.
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
class SicFileCombinerUtil
{


    /**
     * This property holds the combinerFirstSymbol for this instance.
     * This is the symbol to indicate the beginning of a lazy override variable symbol.
     * This should be a one letter long symbol.
     *
     *
     * @var string = $
     */
    protected $lazyOverrideSymbol;

    /**
     * This property holds the variableSymbol for this instance.
     * This is the symbol to indicate the beginning of a variable reference symbol.
     * This should be a one letter long symbol.
     * @var string = $
     */
    protected $variableSymbol;


    /**
     * Builds the SicFileCombinerUtil instance.
     */
    public function __construct()
    {
        $this->lazyOverrideSymbol = '$';
        $this->variableSymbol = '$';
    }

    /**
     * Sets the lazyOverrideSymbol.
     *
     * @param string $lazyOverrideSymbol
     */
    public function setLazyOverrideSymbol(string $lazyOverrideSymbol)
    {
        $this->lazyOverrideSymbol = $lazyOverrideSymbol;
    }

    /**
     * Sets the variableSymbol.
     *
     * @param string $variableSymbol
     */
    public function setVariableSymbol(string $variableSymbol)
    {
        $this->variableSymbol = $variableSymbol;
    }


    /**
     * Combines the babyYaml files found in the given directory, and returns the resulting array.
     * The target merge/replace syntax described above in this class comments applies.
     *
     * @param string $directory
     * @return array
     * @throws SicToolsException
     */
    public function combine(string $directory)
    {
        $ret = [];
        if (is_dir($directory)) {
            $files = YorgDirScannerTool::getFilesWithExtension($directory, "byml", false, true, false);
            $lazyVars = [];

            //--------------------------------------------
            // First combine all files as usual, and extract the merge/replace directives
            //--------------------------------------------
            foreach ($files as $file) {
                $fileConf = BabyYamlUtil::readFile($file);
                foreach ($fileConf as $key => $value) {
                    if ($this->lazyOverrideSymbol === substr($key, 0, 1)) {
                        $lazyVars[substr($key, 1)] = [$value, $file];
                        unset($fileConf[$key]);
                    }
                }
                $ret = ArrayTool::arrayMergeReplaceRecursive([$ret, $fileConf]);
            }


            //--------------------------------------------
            // Now inject the lazy overrides variables
            //--------------------------------------------
            foreach ($lazyVars as $key => $info) {
                list($value, $file) = $info;
                $found = false;
                $targetValue = BDotTool::getDotValue($key, $ret, null, $found);

                if (false === $found) {
                    $targetValue = $value;
                } else {
                    if (is_array($targetValue)) {
                        if (is_array($value)) {
                            $targetValue = array_merge($targetValue, $value);
                        } else {
                            $type = gettype($value);
                            throw new SicToolsException("SicFileCombinerUtil: the injected value must be an array for key $key, $type given, defined in file $file.");
                        }
                    } else {
                        $targetValue = $value;
                    }
                }
                BDotTool::setDotValue($key, $targetValue, $ret);
            }


            //--------------------------------------------
            // Finally, replace the variables references
            //--------------------------------------------
            /**
             * Note: we want to be able to inject non-scalar values, so I first collect the (dot) paths,
             * then to array replacement (rather than using traditional array_walk_recursive method).
             */
            $dotPathsWithVars = [];
            BDotTool::walk($ret, function (&$v, $key, $dotPath) use (&$dotPathsWithVars) {
                if (is_string($v)) {
                    if (false !== strpos($v, $this->variableSymbol . '{')) {
                        if (preg_match('!\\' . $this->variableSymbol . '\{([^\}]+)\}!', $v, $match)) {
                            $dotPathsWithVars[$match[1]] = $dotPath;
                        }
                    }
                }
            });


            foreach ($dotPathsWithVars as $src => $target) {
                $srcFound = false;
                $srcValue = BDotTool::getDotValue($src, $ret, null, $srcFound);
                if (true === $srcFound) {

                    $targetFound = false;
                    $targetValue = BDotTool::getDotValue($target, $ret, null, $targetFound);


                    if (true === $targetFound) {
                        if (is_array($targetValue)) {
                            if (is_array($srcValue)) {
                                $targetValue = array_merge($targetValue, $srcValue);
                            } else {
                                $type = gettype($src);
                                throw new SicToolsException("SicFileCombinerUtil: the target value of $target is an array, and therefore the source value $src must ALSO be an array; $type given.");
                            }
                        } else {
                            $targetValue = $srcValue;
                        }
                    } else {
                        $targetValue = $srcValue;
                    }
                    BDotTool::setDotValue($target, $targetValue, $ret);
                } else {
                    //shouldn't happen, should it?
                    throw new SicToolsException("SicFileCombinerUtil: source was not found: $src, with target $target");
                }
            }
        }
        return $ret;
    }
}