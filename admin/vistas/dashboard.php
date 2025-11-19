<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>Motos Registradas</h3>
            <div class="stat-icon">🏍️</div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['motos_registradas']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>Propietarios</h3>
            <div class="stat-icon">👤</div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['propietarios']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>Registros Activos</h3>
            <div class="stat-icon">📝</div>
        </div>
        <div class="stat-value"><?php echo number_format($stats['registros_activos']); ?></div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <h3>Total Recaudado</h3>
            <div class="stat-icon">💰</div>
        </div>
        <div class="stat-value">$<?php echo $stats['total_recaudado']; ?></div>
    </div>
</div>

<div class="view-placeholder">
    <div class="icon">📊</div>
    <h3>Bienvenido al Panel de Administración</h3>
    <p>Utiliza el menú lateral para navegar entre las diferentes secciones del sistema de parqueadero.</p>
</div>