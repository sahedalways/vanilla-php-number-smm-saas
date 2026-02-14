 <div class="d-flex justify-content-between align-items-center mb-4">
     <div>
         <small class="text-light">Welcome back,</small>
         <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($userName); ?></h5>
     </div>
     <div class="d-flex gap-2">
         <button class="btn btn-outline-secondary border-0 rounded-circle text-white">
             <i class="fa-regular fa-sun"></i>
         </button>
         <button class="btn btn-outline-secondary border-0 rounded-circle text-white position-relative">
             <i class="fa-regular fa-bell"></i>
             <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;"><?php echo $pendingRequests; ?></span>
         </button>
         <!-- Logout Button -->
         <a href="/logout" class="btn btn-outline-danger border-0 rounded-pill fw-bold ms-2">Logout</a>
     </div>
 </div>
