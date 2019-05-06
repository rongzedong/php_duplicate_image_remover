# php_duplicate_image_remover
most image will be upload to the web, but how to control duplicate images? why not remove it, and make a link?    网站上太多重复图片占用空间？为什么不建立个链接，把重复的都指向一个文件，直接删掉重复文件？

文件直接放到根目录下， 通过 htaccess 文件，把所有不存在的请求都指向 index.php 然后 include 这个文件

定期访问这个文件，更新 同名的 json文件并删除重复文件

这个文件会自动读取重复列表，访问重复文件的时候，自动打开那个相同的文件用来代替这个重复文件。（相当于建立了linux 下的 文件 link）
