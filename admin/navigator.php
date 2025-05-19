<div class="w-[250px] bg-(--secondary) h-[95vh] rounded-br-[4rem] fixed top-0 left-0 z-1000 flex flex-col gap-4 p-4">
    <div class="flex flex-col gap-2 p-4">
    <img src="<?php echo $site_url; ?>/<?php echo $site_logo; ?>" alt="logo" class="w-[full] transition-all duration-300">
        <a href="/admin/bloglar.php" class="hover:text-(--primary) text-white duration-300 transition clamp-h5">Bloglar</a>
        <a href="/admin/kategoriler.php" class="hover:text-(--primary) text-white duration-300 transition clamp-h5">Kategoriler</a>
        <a href="/admin/ayarlar.php" class="hover:text-(--primary) text-white duration-300 transition clamp-h5">Ayarlar</a>
    </div>
</div>
<div class="fixed bg-(--secondary) w-8 h-8 top-[100px] left-[250px] z-1000">
    <div class="w-full h-full bg-black rounded-tl-[100%]"></div>
</div>
<div class="w-[calc(100%-250px-2rem)] rounded-br-[4rem] fixed top-0 left-[250px] z-1000 bg-(--secondary)">
    <div class="flex flex-col gap-4 px-4 h-[100px] justify-center">
        <h3 class="clamp-h3"><?php echo $title; ?></h3>
    </div>
</div>