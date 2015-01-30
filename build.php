<?php
/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * Script to build a Magento Extension
 * Sets names in composer.json and sets up directory structure
 */

$checkRequisites = function () {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    if (version_compare(phpversion(), "5.4.0", "<")) {
        throw new Exception("Requires PHP >= 5.4");
    }
};

$getJson = function ($fileName) {
    return json_decode(file_get_contents($fileName), true);
};

$writeJson = function ($fileName, array $data) {
    file_put_contents($fileName, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
};

$updateComposerFile = function ($projectName, $description, $vendor, $nameSpace, $codePool) {
    $content = file_get_contents('composer.json');

    $content = str_replace('__CODEPOOL__', $codePool, $content);
    $content = str_replace('__VENDOR__', $vendor, $content);
    $content = str_replace('__NAMESPACE__', $nameSpace, $content);
    $content = str_replace('__VENDOR_LAYOUT__', strtolower($vendor), $content);
    $content = str_replace('__VENDOR_TEMPLATE__', strtolower($vendor), $content);
    $content = str_replace('__VENDOR_JS__', strtolower($vendor), $content);
    $content = str_replace('__NAMESPACE_JS__', strtolower($nameSpace), $content);


    $json = json_decode($content, true);
    $json['name']           = $projectName;
    $json['description']    = $description;
    file_put_contents('composer.json', json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
};

$initGit = function ($repositoryUrl) {
    exec('rm -rf .git');
    exec('git init');
    exec('git add .');
    exec('git commit -m "Initial Module Setup"');
    exec('git remote add origin ' . $repositoryUrl);
};

$cleanUp = function () {
    unlink(__FILE__);
};

$createReadme = function ($projectName, $repositoryUrl, $description) {
    unlink('README.md');
    $readMe = file_get_contents('README.md.tmpl');

    $readMe = str_replace("{{NAME}}", $projectName, $readMe);
    $readMe = str_replace("{{REPO}}", $repositoryUrl, $readMe);
    $readMe = str_replace("{{PACKAGENAME}}", $projectName, $readMe);
    $readMe = str_replace("{{DESCRIPTION}}", $description, $readMe);

    file_put_contents('README.md', $readMe);
    unlink('README.md.tmpl');
};

$readLine = function ($prompt) {
    echo $prompt;
    $line = fgets(STDIN);
    return trim($line);
};

$askQuestion = function ($question, $validator = null) use ($readLine) {
    $question   = sprintf("\033[30;42m%s?\033[39;49m ", $question);
    $result     = $readLine($question);
    if (is_callable($validator)) {
        $valid = false;
        while (!$valid) {
            try {
                $validator($result);
                $valid = true;
            } catch (\Exception $e) {
                $message = $e->getMessage();
                echo sprintf("\033[37;41m%s\033[39;49m", $message) . "\n";
                $result = $readLine($question);
            }
        }
    }
    return $result;
};

$askQuestionWithOptions = function (array $options, $question) use ($readLine) {
    $options = array_values($options);
    echo sprintf("\033[30;42m%s?\033[39;49m\n\n", $question);
    $result = null;
    while (!isset($options[$result])) {
        foreach ($options as $key => $option) {
            echo sprintf("  \033[32m[%s]\033[39;49m %s\n", $key + 1, $option);
        }
        echo "\n";
        $result = (int) trim($readLine("Enter # to select: "));
        $result -= 1;
    }
    return $options[$result];
};

$buildConfig = function ($vendor, $nameSpace, $codePool) {
    $moduleConfig = 'app/code/codepool/Vendor/Namespace/etc/config.xml';
    $moduleConfigContents = file_get_contents($moduleConfig);

    $moduleConfigContents = str_replace('__VENDOR__', $vendor, $moduleConfigContents);
    $moduleConfigContents = str_replace('__NAMESPACE__', $nameSpace, $moduleConfigContents);
    $moduleConfigContents = str_replace('__VENDOR_LC__', strtolower($vendor), $moduleConfigContents);
    $moduleConfigContents = str_replace('__NAMESPACE_LC__', strtolower($nameSpace), $moduleConfigContents);

    file_put_contents($moduleConfig, $moduleConfigContents);

    $fileName = '__VENDOR_____NAMESPACE__.xml';
    $configContents = file_get_contents('app/etc/modules/' . $fileName);
    $configContents = str_replace('__VENDOR__', $vendor, $configContents);
    $configContents = str_replace('__NAMESPACE__', $nameSpace, $configContents);
    $configContents = str_replace('__CODEPOOL__', $codePool, $configContents);

    $newFileName = str_replace('__VENDOR__', $vendor, $fileName);
    $newFileName = str_replace('__NAMESPACE__', $nameSpace, $newFileName);
    file_put_contents('app/etc/modules/' . $newFileName, $configContents);
    unlink('app/etc/modules/' . $fileName);
};

$buildCodeStructure = function ($vendor, $nameSpace, $codePool) {
    $from = 'app/code/codepool/Vendor/Namespace';
    $to = "app/code/$codePool/$vendor/$nameSpace";

    mkdir(dirname($to), 0777, true);
    rename($from, $to);
    rmdir(dirname($from));
    rmdir(dirname(dirname($from)));

    $folders = array('Block', 'controllers', 'Helper', 'sql/__VENDOR_____NAMESPACE___setup');
    foreach ($folders as $folder) {
        unlink("$to/$folder/.gitkeep");
    }

    $setupFolder = "$to/sql/__VENDOR_____NAMESPACE___setup";
    $destination = str_replace('__VENDOR__', strtolower($vendor), $setupFolder);
    $destination = str_replace('__NAMESPACE__', strtolower($nameSpace), $destination);
    rename($setupFolder, $destination);
};

$buildDesignStructure = function ($vendor, $nameSpace) {
    $layoutFolder   = 'app/design/frontend/base/default/layout/' . strtolower($vendor);
    $templateFolder = 'app/design/frontend/base/default/template/' . strtolower($vendor) . '/' . strtolower($nameSpace);
    $skinJsFolder   = 'skin/frontend/base/default/js/' . strtolower($vendor) . '/' . strtolower($nameSpace);

    mkdir($layoutFolder, 0777, true);
    mkdir($templateFolder, 0777, true);
    mkdir($skinJsFolder, 0777, true);
    unlink('app/design/frontend/base/default/layout/.gitkeep');
    unlink('app/design/frontend/base/default/template/.gitkeep');
    unlink('skin/frontend/base/default/js/.gitkeep');
};

$buildTestStructure = function ($nameSpace) {
    $testDir        = 'test/__NAMESPACE__';
    $destination    = str_replace('__NAMESPACE__', strtolower($nameSpace), $testDir);
    rename($testDir, $destination);
};

$projectName = $askQuestion("Project Name", function ($result) {
    if (!preg_match('/^[a-z][a-z\-]*[a-z]\/[a-z][a-z\-]*[a-z]$/', $result)) {
        throw new Exception("Project name should be lowercase, slash separated e.g. jhhello/social");
    }
});

$parts      = explode("/", $projectName);
$vendor     = implode('', array_map('ucfirst', explode("-", $parts[0])));
$nameSpace  = implode('', array_map('ucfirst', explode("-", $parts[1])));

$description = $askQuestion("Description");

$repositoryUrl = $askQuestion("Repository URL", function ($result) {
    if (!preg_match('/.git$/', $result)) {
        throw new Exception("That doesn't look like a git URL");
    }
});

$codePool = $askQuestionWithOptions(array('local', 'community'), 'Code Pool');

$updateComposerFile($projectName, $description, $vendor, $nameSpace, $codePool);
$buildConfig($vendor, $nameSpace, $codePool);
$buildCodeStructure($vendor, $nameSpace, $codePool);
$buildDesignStructure($vendor, $nameSpace);
$buildTestStructure($nameSpace);
$createReadme($projectName, $repositoryUrl, $description);
$cleanUp();
$initGit($repositoryUrl);

echo "\n\033[30;42mDONE!\033[39;49m\n\n";
