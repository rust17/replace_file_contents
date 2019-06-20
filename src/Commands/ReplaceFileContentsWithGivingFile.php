<?php

namespace Circle33\ReplaceFileContents\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ReplaceFileContentsWithGivingFile extends Command
{
	/** 
	 * @var string
	 *
	 * 命令名
	 */
	protected static $defaultName = 'app:replace-file-contents-with-giving-file';

	/**
	 * @var array
	 */
	protected $info = [
		'DIRECTORY' => '',
		'SUB_DIRECTORY' => '',
		'GIVING_FILE'   => '',
		'EXCLUDES'      => '',
	];

	protected function configure()
	{
		$this->setDescription('使用指定文件的内容替换指定目录下的所有文件的内容');
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

		// 设置给定文件
		$question = new Question('请输入[<fg=yellow>指定文件的路径（相对根目录的路径）</fg=yellow>]，将以该文件内容[<fg=yellow>作为基准</fg=yellow>]进行替换：');
		$question->setValidator(function ($answer) use ($directory) {
			// 改变工作目录
			chdir(dirname($directory . $answer));

			if (! is_file(basename($answer))) {
				throw new \Exception('文件名不存在');
			}

			return $answer;
		});
		$question->setMaxAttempts(5);
		$this->info['GIVING_FILE'] = $givingFile = $helper->ask($input, $output, $question);

		// 设置排除列表
		$question = new Question('请输入[<fg=yellow>需要排除的文件名</fg=yellow>]，多个文件请以逗号隔开：');
		$this->info['EXCLUDES'] = $excludes = $helper->ask($input, $output, $question);

		// 寻找目标文件
		$files = $this->findFiles($directory . $subDirectory);
		// 给定文件
		$givingFile = $directory . $givingFile;
		// 替换文件内容
		$num   = $this->replaceContents($givingFile, $files);

		$output->writeln(\sprintf('<info>总共替换了 %s 个文件</info>', $num));
	}

	protected function findFiles($destination)
	{
		$finder = new Finder();

		return $finder->files()->in($destination);
	}

	protected function replaceContents($givingFile, $files)
	{	
		// 排除文件列表
		$excludes = explode(',', $this->info['EXCLUDES']);

		// 定义一个变量保存修改过的文件数
		$count = 0;

		// 定义一个变量保存文件内容
		$givingFileContent = "";

		// 读取给定文件内容
		$fpr = @fopen($givingFile, "rb") or die ($givingFile . " 文件打开失败");
		while (! feof($fpr)) {
			// 逐行读取文件
			$line = fgets($fpr, 4096);

			$givingFileContent .= $line;
		}

		foreach ($files as $file) {
			// 写入需要替换的文件当中
			// 排除
			if (in_array(basename($file->getRealPath()), $excludes)) {
				continue;
			} else {
				$fpw = @fopen($file->getRealPath(), "wb") or die ($file->getRealPath() . " 文件写入失败");
				fwrite($fpw, $givingFileContent);
				// 计数
				$count ++;

				// 关闭
				fclose($fpw);
			}
		}

		// 关闭给定文件
		fclose($fpr);

		return $count;
	}
}