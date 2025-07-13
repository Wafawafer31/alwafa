<div class="site-mobile-menu site-navbar-target">
    <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close">
            <span class="icofont-close js-menu-toggle"></span>
        </div>
    </div>
    <div class="site-mobile-menu-body"></div>
</div>

<div class="container">
    <nav class="site-nav">
        <div class="logo">
            <a href="/alwafahub/" class="text-white">AlwafaHub</a>
        </div>
        <div class="row align-items-center">
            <div class="col-12 col-sm-12 col-lg-12 site-navigation text-center">
                <ul class="js-clone-nav d-none d-lg-inline-block text-right site-menu">
                    <li><a href="/alwafahub/">Home</a></li>
                    <?php
                    $pages_result = $mysqli->query("SELECT * FROM pages ORDER BY title ASC");
                    while($page = $pages_result->fetch_assoc()):
                    ?>
                    <li><a href="/alwafahub/page/<?php echo $page['slug']; ?>"><?php echo htmlspecialchars($page['title']); ?></a></li>
                    <?php endwhile; ?>
                    <li><a href="/alwafahub/admin/">Admin Login</a></li>
                </ul>
                <a href="#" class="burger light ms-auto float-end site-menu-toggle js-menu-toggle d-inline-block d-lg-none" data-toggle="collapse" data-target="#main-navbar">
                    <span></span>
                </a>
            </div>
        </div>
    </nav>
</div>
