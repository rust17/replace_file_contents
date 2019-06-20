<?php

namespace Circle33\TransformAbsolutePath\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class TransformAbsolutePath extends Command
{
	/** 
	 * @var string
	 *
	 * 命令名
	 */
	protected static $defaultName = 'app:transform-absolute-path';

	/** 
	 * @var array
	 *
	 * 加载文件的正则匹配规则
	 */
	protected $patterns = [
		'/(require)\([\"|\'](.+)[\"|\']\)/m',
		'/(require)[\s]\([\"|\'](.+)[\"|\']\)/m',
		'/(require)\s[\"|\'](.+)[\"|\']/m',
		'/(require_once)\([\"|\'](.+)[\"|\']\)/m',
		'/(require_once)[\s]\([\"|\'](.+)[\"|\']\)/m',
		'/(require_once)\s[\"|\'](.+)[\"|\']/m',
		'/(include)\([\"|\'](.+)[\"|\']\)/m',
		'/(include)[\s]\([\"|\'](.+)[\"|\']\)/m',
		'/(include)\s[\"|\'](.+)[\"|\']/m',
		'/(include_once)\([\"|\'](.+)[\"|\']\)/m',
		'/(include_once)[\s]\([\"|\'](.+)[\"|\']\)/m',
		'/(include_once)\s[\"|\'](.+)[\"|\']/m',
	];

	/**
	 * @var array
	 */
	protected $info = [
		'DIRECTORY' => '',
		'SUB_DIRECTORY' => '',
		'ROOT_PATH_ALIAS' => '',
	];

	protected function configure()
	{
		$this->setDescription('指定目录下所有文件的加载路径由相对路径替换成绝对路径。');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->fs = new Filesystem();
		$helper   = $this->getHelper('question');

		// 设置根目录
		$question = new Question('请输入[<fg=yellow>项目根目录</fg=yellow>]：');
		$question->setValidator(function ($answer) {
			if (! is_dir($answer)) {
				throw new \Exception('该目录无效');
			}

			return $answer;
		});
		$question->setMaxAttempts(5);
		$this->info['DIRECTORY'] = $directory = $helper->ask($input, $output, $question);

		// 设置二级目录
		$question = new Question('请输入[<fg=yellow>根目录下需要替换的二级</fg=yellow>]目录：');
		$question->setValidator(function ($answer) use ($directory) {
			if (! is_dir($directory . $answer)) {
				throw new \Exception('该目录无效');
			}

			return $answer;
		});
		$question->setMaxAttempts(5);
		$this->info['SUB_DIRECTORY'] = $subDirectory = $helper->ask($input, $output, $question);

		// 设置绝对路径别名
		$defaultRootPathAlias = $directory;
		$question = new Question('请输入[<fg=yellow>根目录别名</fg=yellow>]，没有别名将以[<fg=yellow>本地路径</fg=yellow>]替换：', $defaultRootPathAlias);
		$this->info['ROOT_PATH_ALIAS'] = $rootPathAlias = $helper->ask($input, $output, $question);

		// 设置排除列表
		$question = new Question('请输入[<fg=yellow>需要排除的文件名</fg=yellow>]，多个文件请以逗号隔开：');
		$this->info['EXCLUDES'] = $excludes = $helper->ask($input, $output, $question);

		// 寻找目标文件
		$files = $this->findFiles($directory . $subDirectory);
		// 替换文件内容
		$num   = $this->replaceContents($files);

		$output->writeln(\sprintf('<info>总共替换了 %s 次</info>', $num));
	}

	protected function findFiles($destination)
	{
		$finder = new Finder();

		return $finder->files()->in($destination);
	}

	protected function replaceContents($files)
	{	
		// 排除文件列表
		$excludes = explode(',', $this->info['EXCLUDES']);

		// 定义一个变量保存修改过的次数
		$count = 0;

		foreach ($files as $file) {		
			// 改变工作目录
			chdir(dirname($file->getRealPath()));

			// 定义一个变量保存文件内容
			$content = "";

			// 读
			$fpr = @fopen($file->getRealPath(), "rb") or die ($file->getRealPath() . " 文件打开失败");
			// 根据设定的正则模式进行匹配，寻找文件中需要替换的内容
			while (! feof($fpr)) {
				// 逐行读取文件
				$line = fgets($fpr, 4096);
				// 替换
				$line = preg_replace_callback($this->patterns, function ($matches) use ($excludes, &$count) {
					// 排除
					if (in_array(basename($matches[2]), $excludes)) {
						return $matches[0];
					} else {
						$count ++;
						return $matches[1] .' '. $this->info['ROOT_PATH_ALIAS'] .' . \''. str_replace($this->info['DIRECTORY'], '', realpath($matches[2])) .'\'';
					}
				}, $line);
				
				$content .= $line;
			}

			// 写
			$fpw = @fopen($file->getRealPath(), "wb") or die ($file->getRealPath() . " 文件写入失败");
			fwrite($fpw, $content);

			// 关闭
			fclose($fpr);
			fclose($fpw);
		}

		return $count;
	}
}