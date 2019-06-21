### 批量替换文件脚手架工具

1. 使用指定文件内容替换指定目录下的所有文件

### 使用

```shell
composer install

php bin/replace-file-contents-with-giving-file app:replace-file-contents-with-giving-file

# 输入项目的根目录，如 D:/WWW/project/
# 输入二级目录，将在该目录下查找文件进行替换，如 x/folder/
# 输入指定文件的路径（相对根目录的路径），如：x/php/exmple.php
# 输入排除的文件名，如：example.php,example2.php,example3.php
```

2. 将文件中加载的相对路径转成绝对路径

### 使用

```shell
php bin/transform-absolute-path app:transform-absolute-path

# 输入项目的根目录，如 D:/WWW/project/
# 输入二级目录，将在该目录下查找文件进行替换，如 x/folder/
# 输入根目录别名，如：ROOT_PATH，没有别名将以本地绝对路径替换，如 ROOT_ALIAS
# 输入排除的文件名，如：example.php,example2.php,example3.php

# 替换前：require_once '../../php/exmaple.php';
# 替换后：require_once ROOT_ALIAS . 'x/php/example.php';

# 替换前：require '../../php/exmaple.php';
# 替换后：require ROOT_ALIAS . 'x/php/example.php';

# 替换前：include_once '../../php/exmaple.php';
# 替换后：include_once ROOT_ALIAS . 'x/php/example.php';

# 替换前：include '../../php/exmaple.php';
# 替换后：include ROOT_ALIAS . 'x/php/example.php';
```

### License

MIT

### reference

[overtrue/package-builder](https://github.com/overtrue/package-builder)
[symfony/symfony](https://github.com/symfony/symfony)