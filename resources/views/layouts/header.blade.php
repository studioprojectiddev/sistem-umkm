<style>
    .nav-mode {
        background: #2563eb;
        color: #fff !important;
        border-radius: 999px;
        padding: 8px 16px;

        display: flex;
        align-items: center;
        gap: 8px;

        margin-right: 24px; /* ⬅️ JARAK KE PROFILE */

        transition: all .2s ease;
    }

    .nav-mode i {
        font-size: 18px;
    }

    .nav-mode-text {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .nav-mode:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }

</style>
<nav>
    <i class='bx bx-menu toggle-sidebar' ></i>
    <form action="#">
        <div class="form-group">
            <input type="text" placeholder="Search...">
            <i class='bx bx-search icon' ></i>
        </div>
    </form>
    <a href="{{ route('umkm.kasir.index') }}"
        class="nav-link nav-mode"
        title="Mode Kasir (Tablet)">
        <i class='bx bx-store'></i>
        <span class="nav-mode-text">My Store</span>
    </a>
    <span class="divider"></span>
    <div class="profile">
        <img src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixid=MnwxMjA3fDB8MHxzZWFyY2h8NHx8cGVvcGxlfGVufDB8fDB8fA%3D%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" alt="">
        <ul class="profile-link">
            <li><a href="#"><i class='bx bxs-user-circle icon' ></i> Profile</a></li>
            <li><a href="#"><i class='bx bxs-cog' ></i> Settings</a></li>
            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class='bx bxs-log-out-circle'></i> Logout
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</nav>