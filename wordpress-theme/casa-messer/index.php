<?php get_header(); ?><main class="entry"><h1><?php bloginfo('name'); ?></h1><?php if(have_posts()): while(have_posts()): the_post(); the_content(); endwhile; endif; ?></main><?php get_footer(); ?>
