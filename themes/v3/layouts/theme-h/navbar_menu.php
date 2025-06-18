<!-- Begin Left Navigation -->
<style>
   .horizontal-topnav .navbar-nav .nav-link {
    color: var(--bs-gray-800);
}

.nav-link.active {
  border-bottom: 3px solid #6ba0e5;
   color: var(--bs-primary);
   font-weight: 500;
   
}

.nav-link:hover {
   border-bottom: 3px solid #6ba0e5;
   color: var(--bs-gray-800);
}

.navbar-nav .nav-link.active, .navbar-nav .nav-link.show {
  color: var(--bs-primary);
}
</style>      
<div class="horizontal-topnav shadow-sm">
         <div class="container-fluid">
            <nav class="navbar navbar-expand-lg topnav-menu">
               <div id="topnav-menu-content" class="collapse navbar-collapse">
                  <?php if(isset($this->blocks['navbar_menu']) && $this->blocks['navbar_menu']):?>
                  <ul id="side-menu" class="navbar-nav d-flex gap-2">
                     <?= $this->blocks['navbar_menu'];?>
               
                  <?php endif?>
                     
                  </ul>
               </div>
            </nav>
         </div>
      </div>
      <!-- Left Navigation End -->