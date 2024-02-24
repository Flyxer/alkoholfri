<header class="banner">
  <div class="container">
    <a class="brand" href="{{ home_url('/') }}">
      <?php
        if(file_exists(get_template_directory()."/resources/images/pfclogo.svg"))
            print file_get_contents(get_template_directory()."/resources/images/pfclogo.svg");
        else
            print $siteName;
      ?>
    </a>

    @if (has_nav_menu('primary_navigation'))
      <button data-collapse-toggle="navbar-hamburger" type="button"
              class="inline-flex items-center justify-center p-2 w-10 h-10 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
              aria-controls="navbar-hamburger" aria-expanded="false">
        <span class="sr-only">Open main menu</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M1 1h15M1 7h15M1 13h15"/>
        </svg>
      </button>

      <nav class="nav-primary" aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}" id="navbar-hamburger">
        {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav', 'echo' => false]) !!}
      </nav>
    @endif
  </div>
</header>
