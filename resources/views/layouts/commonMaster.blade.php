<!DOCTYPE html>
<html class="light-style layout-menu-fixed" data-theme="theme-default" data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel" data-template="vertical-menu-laravel-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <meta name="viewport" content="width=device-width" />
  <title>@yield('title') | INVTEK </title>
  <meta name="description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

  <!-- Include Styles -->
  @include('layouts/sections/styles')
  @yield('page-style')

  <!-- Theme Toggle Styles -->
  <style>
    .menu-vertical .menu-item .menu-link>div:not(.badge) {
      white-space: initial;
    }

    /* Theme Toggle Button Styles */
    .theme-toggle {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      background: var(--bs-primary);
      color: white;
      border: none;
      border-radius: 50%;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      cursor: pointer;
    }


    .theme-toggle i {
      font-size: 18px;
    }

    /* Dark theme overrides */
    .dark-style .theme-toggle {
      background: var(--bs-warning);
      color: var(--bs-dark);
    }

    /* BLOQUE FUNCIONAL DE TEMA OSCURO */
    .dark-style body {
      background-color: #23272F !important;
      color: #f0f0f0 !important;
    }

    .dark-style .card,
    .dark-style .navbar,
    .dark-style .dropdown-menu,
    .dark-style .sidebar,
    .dark-style .modal-content {
      background-color: #23272F !important;
      color: #fff !important;
    }

    .dark-style .navbar,
    .dark-style .navbar * {
      background-color: #23272F !important;
      color: #fff !important;
    }

    .dark-style .btn-primary {
      background-color: #4e5d94 !important;
      border-color: #4e5d94 !important;
    }

    .dark-style .btn-secondary {
      background-color: #343a40 !important;
      border-color: #343a40 !important;
    }

    .dark-style .table {
      color: #fff !important;
      background-color: #23272F !important;
    }

    .dark-style .table-striped>tbody>tr:nth-of-type(odd) {
      background-color: #22252a !important;
    }

    .dark-style a {
      color: #90caf9 !important;
    }

    .dark-style .text-muted {
      color: #b0b0b0 !important;
    }

    .dark-style hr {
      border-color: #444 !important;
    }

    .dark-style .dropdown-menu {
      background-color: #23272F !important;
      color: #fff !important;
    }

    .dark-style .modal-content {
      background-color: #23272F !important;
      color: #fff !important;
    }

    /* Smooth transitions for theme changes */
    * {
      transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    /* Custom theme persistence indicator */
    .theme-indicator {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 8px 12px;
      background: rgba(0, 0, 0, 0.7);
      color: white;
      border-radius: 20px;
      font-size: 12px;
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 999;
    }

    .theme-indicator.show {
      opacity: 1;
    }

    .dark-style .theme-indicator {
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .descripcionSeleccion {
      color: #888;
      font-style: italic;
      margin-top: 10px;
    }
  </style>

  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes')
</head>

<body>
  <!-- Theme Toggle Button -->
  <button style="display: none;" class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
    <i class="bx bx-moon" id="theme-icon"></i>
  </button>

  <!-- Theme Indicator -->


  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->

  <!-- Include Scripts -->
  @include('layouts/sections/scripts')

  <!-- Theme Toggle Script -->
  <script>
    // ============================================
    // SISTEMA DE CAMBIO DE TEMA CLARO/OSCURO
    // ============================================
    
    // 1. FUNCIONES DE ALMACENAMIENTO
    function getStoredTheme() {
      return localStorage.getItem('invtek-theme') || null;
    }
    
    function setStoredTheme(theme) {
      localStorage.setItem('invtek-theme', theme);
      //console.log('✅ Tema guardado:', theme);
    }
    
    // 2. FUNCIÓN PRINCIPAL PARA CAMBIAR TEMA
    function setTheme(theme) {
      const html = document.documentElement;
      const body = document.body;
      const themeIcon = document.getElementById('theme-icon');
     // const themeText = document.getElementById('theme-text');
      const themeIndicator = document.getElementById('theme-indicator');
      
      //console.log('🎨 Cambiando tema a:', theme);
      
      /* if (theme === 'dark') {
        // ACTIVAR TEMA OSCURO
        html.classList.remove('light-style');
        html.classList.add('dark-style');
        html.setAttribute('data-theme', 'theme-dark');
        
        // Cambiar icono a sol (para volver a claro)
        themeIcon.className = 'bx bx-sun';
       // themeText.textContent = 'Modo Oscuro Activado';
        
        //console.log('🌙 Tema oscuro activado');
        
      } else { */
        // ACTIVAR TEMA CLARO
        html.classList.remove('dark-style');
        html.classList.add('light-style');
        html.setAttribute('data-theme', 'theme-default');
        
        // Cambiar icono a luna (para ir a oscuro)
        themeIcon.className = 'bx bx-moon';
       // themeText.textContent = 'Modo Claro Activado';
        
        //console.log('☀️ Tema claro activado');
      //}
      
      // Mostrar indicador temporal
      if (themeIndicator) {
        themeIndicator.classList.add('show');
        setTimeout(() => {
          themeIndicator.classList.remove('show');
        }, 2000);
      }
      
      // Guardar preferencia
      setStoredTheme(theme);
      
      // Disparar evento personalizado para otros componentes
      window.dispatchEvent(new CustomEvent('themeChanged', { 
        detail: { 
          theme: theme,
          timestamp: new Date().toISOString()
        } 
      }));
    }
    
    // 3. FUNCIÓN PARA ALTERNAR ENTRE TEMAS
    function toggleTheme() {
      const html = document.documentElement;
      const currentTheme = html.classList.contains('dark-style') ? 'dark' : 'light';
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      
      //console.log(`🔄 Alternando tema: ${currentTheme} → ${newTheme}`);
      setTheme(newTheme);
    }
    
    // 4. DETECTAR PREFERENCIA DEL SISTEMA
    function getSystemTheme() {
      //return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      return 'light';
    }
    
    // 5. OBTENER TEMA PREFERIDO (guardado o sistema)
    function getPreferredTheme() {
      const stored = getStoredTheme();
      if (stored) {
        //console.log('📱 Usando tema guardado:', stored);
        return stored;
      }
      
      const system = getSystemTheme();
      //console.log('🖥️ Usando tema del sistema:', system);
      return system;
    }
    
    // 6. INICIALIZACIÓN AL CARGAR LA PÁGINA
    document.addEventListener('DOMContentLoaded', function() {
      //console.log('🚀 Inicializando sistema de temas...');
      
      const preferredTheme = getPreferredTheme();
      setTheme(preferredTheme);
      
      // Escuchar cambios en la preferencia del sistema
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!getStoredTheme()) {
          //console.log('🔄 Cambio detectado en tema del sistema');
          //setTheme(e.matches ? 'dark' : 'light');
          setTheme('light');
        }
      });
      
      //console.log('✅ Sistema de temas inicializado correctamente');
    });
    
    // 7. ATAJO DE TECLADO (Ctrl/Cmd + Shift + T)
    document.addEventListener('keydown', function(e) {
      if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'T') {
        e.preventDefault();
        //console.log('⌨️ Atajo de teclado activado');
        toggleTheme();
      }
    });
    
    // 8. FUNCIONES ADICIONALES
    function resetThemeToSystem() {
      //console.log('🔄 Reseteando tema a preferencia del sistema');
      localStorage.removeItem('invtek-theme');
      const systemTheme = getSystemTheme();
      //setTheme(systemTheme);
      setTheme('light');
    }
    
    function getCurrentTheme() {
      return document.documentElement.classList.contains('dark-style') ? 'dark' : 'light';
    }
    
    // 9. HACER FUNCIONES GLOBALMENTE DISPONIBLES
    window.toggleTheme = toggleTheme;
    window.setTheme = setTheme;
    window.resetThemeToSystem = resetThemeToSystem;
    window.getCurrentTheme = getCurrentTheme;
    
    // 10. INFORMACIÓN DE DEBUG (solo en desarrollo)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      //console.log(`
      /* 🎨 SISTEMA DE TEMAS INVTEK
      ========================
      Funciones disponibles:
      - toggleTheme()         → Alterna entre claro/oscuro
      - setTheme('dark')      → Establece tema oscuro
      - setTheme('light')     → Establece tema claro
      - getCurrentTheme()     → Obtiene tema actual
      - resetThemeToSystem()  → Resetea a preferencia del sistema
      
      Atajo: Ctrl/Cmd + Shift + T
      `); */
    }
  </script>
</body>

</html>