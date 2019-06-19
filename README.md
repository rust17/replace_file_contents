### 将文件中加载的相对路径转成绝对路径

### 使用

```shell
composer install

php bin/console app:correct-to-absolute-path

# 输入项目的根目录
# 输入二级目录，将在该目录下查找文件进行替换
# 输入根目录别名，如：ROOT_PATH，没有别名将以本地绝对路径替换
# 输入排除的文件名，如：example.php,example2.php,example3.php
```

### License

MIT

### reference

[overtrue/package-builder](https://github.com/overtrue/package-builder)