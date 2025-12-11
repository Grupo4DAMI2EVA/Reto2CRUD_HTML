 // Verifica la sesión del usuario en cada página

async function comprobarSesion() {
    try {
        const response = await fetch('../../api/me.php', {
            method: 'GET',
            credentials: 'include', 
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.status === 401) {
            // No hay sesión válida
            console.log('Sesión expirada o no válida');
            window.location.href = 'login.html';
            return null;
        }

        if (response.ok) {
            const userData = await response.json();
            localStorage.setItem('actualProfile', JSON.stringify(userData));
            return userData;
        } else {
            console.error('Error al verificar sesión');
            window.location.href = 'login.html';
            return null;
        }
    } catch (error) {
        console.error('Error en comprobarSesion:', error);
        window.location.href = 'login.html';
        return null;
    }
}

async function logout() {
    try {
        const response = await fetch('../../api/logout.php', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            localStorage.removeItem('actualProfile');
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Error al hacer logout:', error);
        window.location.href = 'login.html';
    }
}
