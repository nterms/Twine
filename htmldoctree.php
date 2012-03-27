<?php
define('DS', DIRECTORY_SEPARATOR);

$log = array('info', 'error');
$info = $log['info'] = array();
$error = $log['error'] = array();
$compile = true;

if(!isset($_POST['generate'])) {
    $compile = false;
}
$baseUrl    = isset($_POST['base_url']) ? $_POST['base_url'] : 'http://www.example.com';
$deployPath = isset($_POST['deploy']) ? $_POST['deploy'] : '/docs';
$path       = dirname(__FILE__);
$template   = isset($_POST['template']) ? $_POST['template'] : $path . DS . 'template.html';
$sourceDir  = isset($_POST['source']) ? $_POST['source'] : $path . DS . 'docs-src';
$targetDir  = isset($_POST['target']) ? $_POST['target'] : $path . DS . 'docs';
$breadcrumbSep = isset($_POST['breadcrumb']) ? $_POST['breadcrumb'] : '&gt;';

$deployDir  = $targetDir;

// Load the template
$tempFile = fopen($template, 'r');
$temp = fread($tempFile, 1048576);

// Load the contents

/*if(is_dir($deployDir) && is_writable($targetDir)) {
    echo "Deleting <b>deploy</b> directory: <em>" . $deployDir . '</em><br/>';
    unlink($deployDir) or die("Deleting <b>deploy</b> directory failed: <em>" . $deployDir . '</em><br/>');
} */

if(!is_dir($deployDir)) {
    $info[] = "Creating <b>deploy</b> directory: <em>" . $deployDir . '</em>';
    if(!mkdir($deployDir)) {
        $error[] = "Creating <b>deploy</b> directory failed: <em>" . $deployDir . '</em>';
    }
    chmod($deployDir, 0777);
}

//$menu = getMenu($sourceDir);
//$temp = str_replace("<!-- {menu} -->", $menu, $temp);
if($compile && count($error) == 0) {
    $logs = render($sourceDir, $deployDir, $temp, $deployPath, $baseUrl, $deployPath, array(), $breadcrumbSep);
    $info = array_merge($info, $logs['info']);
    $error = array_merge($error, $logs['error']);
}

function render($sourceDir, $targetDir, $template, $dir, $baseUrl, $relativeUrl = '/', $breadcrumbs = array(), $breadcrumbSeparator) {
    $info = array();
    $error = array();
    $htmldoctreeInfo = '<p>Generated with <a href="http://www.nterms.com/tools/htmldoctree">HTMLDocTree</a></p>';
    
    $info[] = "Entering directory: <em>" . $sourceDir . '</em><br/>';
    $source = opendir($sourceDir);
    $target = opendir($targetDir);
    
    // generate the breadcrumb
    $meta = array();              
    if(is_file($sourceDir . DS . 'index.html')) {
        $content = getFileContent($sourceDir . DS . 'index.html');
        $meta = getMetadata($content);
    }
    
    $title = isset($meta['title']) ? $meta['title']: basename($dir);
    $dirUrl = $baseUrl . $relativeUrl;
    $breadcrumbs[] = ' <span class="breadcrumb-node link"><a href="' . $dirUrl . '" title="' . $title . '" >' . $title . '</a></span>';
                    
    if($source) {
        if($target) {
            while($item = readdir($source)) {
                if($item == '.' || $item == '..') {
                    continue;
                }
                
                $srcPath = $sourceDir . DS . $item;
                $destPath = $targetDir . DS . $item;
                
                $info[] = "Processing node: <em>" . $srcPath . '</em><br/>';
					
                if(is_dir($srcPath)) {
                    					
                    if(!is_dir($destPath)) {
                        //unlink($path);
                        $info[] = "Creating directory: <em>" . $destPath . '</em><br/>';
                        if(!mkdir($destPath)) {
                            $error[] = "Creating directory failed: <em>" . $destPath . '</em><br/>';
                            return;
                        }
                    }
                    
                    render($srcPath, $destPath, $template, $dir . DS . $item, $baseUrl, $relativeUrl . '/' . $item, $breadcrumbs, $breadcrumbSeparator);
                }
                
                if(is_file($srcPath)) {
                    if(strpos($srcPath, ".html") > 0) {
                        $itemId = str_replace(".html", '', $item);
                        $pageId = $dir . '/' . $itemId;
                        $pageUrl = $baseUrl . $dir . '/' . $item;
                        $replacementPatterns = array(
                            '/<!-- *{content} *-->/i',
                            '/<!-- *{breadcrumb} *-->/i',
                            '/<!-- *{title} *-->/i',
                            '/<!-- *{description} *-->/i',
                            '/<!-- *{disqus_identifier} *-->/i',
                            '/<!-- *{disqus_url} *-->/i',
                            '/<!-- *{htmldoctree} *-->/i',
                        );
                        
                        $replacementStrings = array(
                            'content',
                            'beadcrumb',
                            'title',
                            'description',
                            $pageId,
                            $pageUrl,
                            $htmldoctreeInfo
                        );
                        
                        $info[] = "Creating file: <em>" . $destPath . '</em>';
                        $dest = fopen($destPath, 'w');
                        if(!$dest) {
                            $error[] = "Creating file failed: <em>" . $destPath . '</em>';
                        }
                        
                        $content = getFileContent($srcPath);
                        // get metadata if available
                        $meta = getMetadata($content);
                        $html = setMetadata($meta, $html);
                        
                        $replacementStrings[0] = $content;
                                          
                        //$html = str_replace("<!-- {content} -->", $content, $template);
						
                        
                        //$html = str_replace("<!-- {disqus_identifier} -->", $pageId, $html);
                        //$html = str_replace("<!-- {disqus_url} -->", $pageUrl, $html);
                        //$html = str_replace("<!-- {htmldoctree} -->", $htmldoctreeInfo, $html);
						
                        // page title and description
						$title = isset($meta['title']) ? $meta['title']: str_replace('-', ' ', ucfirst($itemId));
						$description = isset($meta['description']) ? $meta['description']: '';
                        $replacementStrings[2] = $title;
                        $replacementStrings[3] = $description;
                        
                        // breadcrumbs
						$itemUrl = $baseUrl . $relativeUrl . '/' . $item;
                        $itemBreadcrumbs = $breadcrumbs;
						$itemBreadcrumbs[] = '<span class="breadcrumb-node last">' . $title . '</span>';
						$breadcrumb = implode(' <span class="breadcrumb-node separator"> ' . $breadcrumbSeparator . ' </span> ', $itemBreadcrumbs);
                        $replacementStrings[1] = $breadcrumb;
                        
						//$breadcrumb .= ' &gt; ' . $title; //'<a href="' . $itemUrl . '" title="' . $title . '" >' . $title . '</a>';
						$html = preg_replace($replacementPatterns, $replacementStrings, $template, 1);
                        //$breadcrumbs = array();
                        fwrite($dest, $html);
                        fclose($dest);
                    } else {
                        $info[] = "Copying file: <em>" . $srcPath . '</em>';
                        if(!copy($srcPath, $destPath)) {
                            $error[] = "Copying file failed: <em>" . $srcPath . '</em>';
                        }
                    }
                    
                }
            }
        } else {
            $error[] = "Processing failed on directory: <em>" . $targetDir . '</em><br/>';
        }
    } else {
        $error[] = "Processing failed on directory: <em>" . $sourceDir . '</em><br/>';
    }
    
    return array('info' => $info, 'error'  => $error);
}

function getMetadata($content)
{
	$meta = array();
	$metaInfoMatches = array();
	$metaInfo = preg_match_all('/<!-- *{([a-z]*)}(.*)-->/i', $content, $metaInfoMatches);
	$metaInfoItems = isset($metaInfoMatches[1]) ? $metaInfoMatches[1] : null;
	$metaInfoValues = isset($metaInfoMatches[2]) ? $metaInfoMatches[2] : null;
	if(count($metaInfoItems)) {
		for ($i=0; $i < count($metaInfoItems); $i++) {
			$meta[trim($metaInfoItems[$i])] = trim($metaInfoValues[$i]);
		}
	}
	
	return $meta;
}

function setMetadata($meta, $html)
{
	if(count($meta)) {
		foreach ($meta as $key => $value) {
			switch ($key) {
				case 'title':
					$html = str_replace("<!-- {title} -->", $value, $html);
					break;
				case 'description':
					$html = str_replace("<!-- {description} -->", $value, $html);
					break;
				default:

					break;
			}
		}
	}
	
	return $html;
}

function getFileContent($file)
{
	$src = fopen($file, 'r');
	$content = fread($src, 1048576);
	fclose($src);
	return $content;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>HTMLDocTree - Folder Based HTML Documentation Compiler</title>
        <style type="text/css">
            body {
                color: #555;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }
            a {
                color: #0077CC;
                text-decoration: none;
            }
            a:hover, a:active, a:visited {
                color: #00aadd;
                text-decoration: underline;
            }
            h1 {
                font-size: 160%;
                margin: 1em 0 0.5em;
            }
            h2 {
                font-size: 140%;
                margin: 1em 0 0.5em;
            }
            p {
                border-bottom: 1px solid #0077CC;
                margin: 4px 0 1em;
                padding-bottom: 1em;
            }
            #main {
                margin: 0 auto;
                width: 800px;
            }            
            /* Form */
            .button {
                background-color: #f5f5f5;
                background-image: linear-gradient(bottom, rgb(221,221,221) 0%, rgb(255,255,255) 100%);
                background-image: -o-linear-gradient(bottom, rgb(221,221,221) 0%, rgb(255,255,255) 100%);
                background-image: -moz-linear-gradient(bottom, rgb(221,221,221) 0%, rgb(255,255,255) 100%);
                background-image: -webkit-linear-gradient(bottom, rgb(221,221,221) 0%, rgb(255,255,255) 100%);
                background-image: -ms-linear-gradient(bottom, rgb(221,221,221) 0%, rgb(255,255,255) 100%);
                border: 1px solid #aaa;
                border-radius: 3px;
                box-shadow: 0 0 4px #ccc;
                cursor: pointer;
                font-weight: bold;
                margin: 1em 0;
                padding: 8px;
                text-shadow: 0 1px 1px #ffffff;
            }
            .form {
                width: 100%;
            }
            .form .row {
                background-color: #def;
                border: 1px solid #acd;
                border-radius: 3px;
                margin: 4px 0;
                position: relative;
                width: 100%;
            }
            .form .row:hover {
                border: 1px solid #8ac;
            }
            .form .row label {
                color: #333;
                display: inline-block;
                font-weight: bold;
                margin: 1em 1%;
                text-shadow: 0 1px 1px #ffffff;
                vertical-align: middle;
                width: 30%;
            }
            .form .row  .inputbox {
                border: 1px solid #abc;
                color: #555;
                display: inline-block;
                height: 24px;
                line-height: 22px;
                padding: 2px 4px;
                vertical-align: middle;
                width: 66%;
            }
            .form .submit, .form .submit:hover {
                background: none;
                border: none;
                text-align: right;
            }
            .form .submit input {
            }
            /* Process  Log */
            .log {
                list-style-type: none;
                margin: 0;
                padding: 0;
            }
            .log li {
                margin: 2px 0;
                padding: 4px;
            }
            .info li {
                background: #dfe;
                color: #060;
            }
            .error li {
                background: #fcc;
                color: #a00;
            }
            /* Footer */
            #footer {
                margin: 2em 0;
                padding: 4px 0 0;
            }
            #footer p {
                font-size: 11px;
                padding: 4px 0;
            }
            /* Tooltips */
            .field-info {
                display: block;
                position: relative;
            }
            .field-info span {
                background: #8ac;
                border-radius: 8px;
                color: #fff;
                display: block;
                font-size: 11px;
                font-weight: bold;
                height: 16px;
                left: 230px;
                line-height: 16px;
                position: absolute;
                text-align: center;
                top: -27px;
                width: 16px;
            }
            .tooltip {
                background: #fffeee;
                border-top: 1px solid #fff;
                border-radius: 0 0 3px 3px;
                color: #666;
                display: none;
                font-size: 11px;
                margin: 0;
                padding: 1px;
                position: relative;
            }
            .tooltip p {
                border: none;
                margin: 6px;
            }
            .tooltip .tiptag {
                background-color: #8ac;
                height: 12px;
                left: 237px;
                position: absolute;
                top: -13px;
                width: 2px;
            }
            .field-info:hover {
                border-top: 1px solid #8ac;
                text-decoration: none;
            }
            .field-info:hover .tooltip {
                display: block;
            }
        </style>
    </head>
    <body>
        <div id="main">
            <h1>HTMLDocTree - Documentation Compiler Tool</h1>
            <p>Folder based HTML document compiler and template engine</p>
            <div id="parameters" class="form">
                <form method="post" action="htmldoctree.php">
                    <div class="row">
                        <label for="base_url">Base URL:</label><input type="text" id="base_url" name="base_url" value="<?php echo $baseUrl; ?>" class="inputbox" />
                        <a href="#" class="field-info"><span>?</span>
                            <div class="tooltip">
                                <p>The URL you wish to publish the documentation at. Do not use the trailing <em>/</em></p>
                                <p>Eg: http://www.example.com</p>
                                <div class="tiptag"></div>
                            </div>
                        </a>
                    </div>
                    <div class="row">
                        <label for="deploy">Deploy Path:</label><input type="text" id="deploy" name="deploy" value="<?php echo $deployPath; ?>" class="inputbox" />
                        <a href="#" class="field-info"><span>?</span>
                            <div class="tooltip">
                                <p>Deploy path relative to the <em>Base URL</em></p>
                                <p>Eg: If you wish to publish this documentation at <em>http://www.example.com/docs</em>, simply enter <em>/docs</em> here.</p>
                                <div class="tiptag"></div>
                            </div>
                        </a>
                    </div>
                    <div class="row">
                        <label for="source">Source Path:</label><input type="text" id="source" name="source" value="<?php echo $sourceDir; ?>" class="inputbox" />
                        <a href="#" class="field-info"><span>?</span>
                            <div class="tooltip">
                                <p>The path where the source files are saved in your web server's file system.</p>
                                <div class="tiptag"></div>
                            </div>
                        </a>
                    </div>
                    <div class="row">
                        <label for="target">Target Path:</label><input type="text" id="target" name="target" value="<?php echo $targetDir; ?>" class="inputbox" />
                        <a href="#" class="field-info"><span>?</span>
                            <div class="tooltip">
                                <p>The path where the resulting documentation file tree should be saved.</p>
                                <div class="tiptag"></div>
                            </div>
                        </a>
                    </div>
                    <div class="row">
                        <label for="template">Template:</label><input type="text" id="template" name="template" value="<?php echo $template; ?>" class="inputbox" />
                        <a href="#" class="field-info"><span>?</span>
                            <div class="tooltip">
                                <p>The psth of the template file</p>
                                <div class="tiptag"></div>
                            </div>
                        </a>
                    </div>
                    <div class="row">
                        <label for="breadcrumb">Breadcrumb Separator:</label><input type="text" id="breadcrumb" name="breadcrumb" value="<?php echo $breadcrumbSep; ?>" class="inputbox" />
                        <a href="#" class="field-info"><span>?</span>
                            <div class="tooltip">
                                <p>The character used to separate the segments of the breadcrumb.</p>
                                <div class="tiptag"></div>
                            </div>
                        </a>
                    </div>
                    <div class="row submit">
                        <input type="submit" value="Generate" class="button" />
                        <input type="hidden" name="generate" value="1" />
                    </div>
                </form>
            </div>
            
            <div id="log">
                <?php if(count($info) > 0 || count($error) > 0) { ?>
                <h2>Process Summary</h2>
                <?php } ?>
                <?php if(count($info) > 0) { ?>
                    <ul class="log info">
                    <?php foreach ($info as $msg) { ?>
                        <li><?php echo $msg; ?></li>
                    <?php } ?>
                    </ul>
                <?php } ?>
                
                <?php if(count($error) > 0) { ?>
                    <ul class="log error">
                    <?php foreach ($error as $msg) { ?>
                        <li><?php echo $msg; ?></li>
                    <?php } ?>
                    </ul>
                <?php } ?>
            </div>
            
            <div id="footer">
                <p>Developed by <a href="http://blog.nterms.com">Saranga Abeykoon</a> | follow on <a href="https://twitter.com/amisaranga">Twitter</a></p>
            </div>
            
        </div>
    </body>
</html>
 
