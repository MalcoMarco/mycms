# MyCMS - Multi-Tenant Landing Page Builder

Una plataforma de creación de landing pages multi-tenant desarrollada con Laravel 12, que permite a los usuarios crear y administrar múltiples sitios web personalizados similares a WordPress.

## Tecnologías Utilizadas

- **Laravel 12** - Framework PHP moderno
- **Flux UI** - Biblioteca de componentes UI para Livewire
- **Livewire 4** - Framework reactivo para interfaces dinámicas
- **Tailwind CSS** - Framework CSS utilitario
- **Tenancy for Laravel** (`stancl/tenancy: ^3.9`) - Gestión de múltiples tenants

## Características Principales

- **Multi-tenancy**: Los usuarios pueden crear múltiples tenants independientes
- **Landing Page Builder**: Editor visual para crear páginas personalizadas
- **Gestión de Dominios**: Cada tenant puede tener su propio dominio
- **Interface Reactiva**: UI construida con Livewire para una experiencia fluida
- **Componentes Modernos**: Uso de Flux UI para componentes consistentes

## Instalación y Configuración

### Prerrequisitos

- PHP 8.2+
- Composer
- Node.js & npm
- PostgreSQL
- dnsmasq (para desarrollo local)

### Configuración de dnsmasq

Para que el proyecto funcione correctamente en local con `mycms.test`, configura dnsmasq:

```bash
# En macOS con Homebrew
brew install dnsmasq

# Configura dnsmasq para resolver .test a localhost
echo 'address=/.test/127.0.0.1' >> $(brew --prefix)/etc/dnsmasq.conf

# En Linux
sudo apt-get install dnsmasq
echo 'address=/.test/127.0.0.1' | sudo tee -a /etc/dnsmasq.conf

# Reinicia dnsmasq
sudo brew services restart dnsmasq  # macOS
sudo systemctl restart dnsmasq     # Linux

# Configura tu sistema para usar dnsmasq como DNS
# macOS: Agregar 127.0.0.1 a los servidores DNS en Configuración del Sistema
# Linux: Editar /etc/resolv.conf o usar NetworkManager
```

### Instalación del Proyecto

1. **Clona el repositorio e instala dependencias:**
   ```bash
   composer install
   npm install
   ```

2. **Configura el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configura la base de datos:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

4. **Compila los assets:**
   ```bash
   npm run build
   ```

## Desarrollo

### Levantar el Proyecto

Para iniciar el entorno de desarrollo completo:

```bash
composer run dev
```

Este comando inicia:
- Servidor PHP de desarrollo
- Cola de trabajos
- Logs en tiempo real con Pail
- Vite para hot reloading de assets

### Comandos Útiles

```bash
# Solo el servidor web
php artisan serve

# Compilar assets para desarrollo
npm run dev

# Compilar assets para producción
npm run build

# Ejecutar tests
composer test

# Formatear código
composer run lint

# Ver logs en tiempo real
php artisan pail

# Ejecutar las migraciones y los seeders
php artisan migrate:refresh --seed
```

## Arquitectura Multi-Tenant

El proyecto utiliza `stancl/tenancy` para proporcionar aislamiento completo entre tenants:

- **Base de datos separadas**: Cada tenant tiene su propia base de datos
- **Dominios personalizados**: Soporte para subdominios y dominios personalizados
- **Aislamiento de archivos**: Storage separado por tenant
- **Configuración independiente**: Cada tenant puede tener su propia configuración

## Estructura del Proyecto

```
app/
├── Models/
│   ├── Tenant.php          # Modelo principal de tenant
│   └── User.php            # Usuarios del sistema
├── Livewire/              # Componentes Livewire
└── Http/
    └── Controllers/       # Controladores de la aplicación

resources/
├── views/
│   ├── flux/             # Componentes Flux personalizados
│   └── layouts/          # Layouts de la aplicación
└── js/                   # Assets JavaScript

routes/
├── web.php              # Rutas principales
└── tenant.php          # Rutas específicas de tenant
```

## Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -am 'Añade nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la licencia MIT.