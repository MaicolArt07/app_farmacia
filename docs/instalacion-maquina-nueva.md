# Instalación en una máquina nueva (arranque automático)

Guía para dejar el sistema funcionando en una computadora nueva (ej. la de
ventas/farmacia), de forma que **al encender la máquina se abran solos WAMP y
el navegador ya con el sistema cargado**, listo para que el trabajador
ingrese con su usuario y contraseña.

No requiere empaquetar la app como escritorio (se intentó con NativePHP y se
descartó, ver `CLAUDE.md`). Esta es la forma que sí funciona: WAMP corriendo
como servicio de Windows + un acceso directo que abre el navegador en modo
"app" apuntando al dominio local del sistema.

## 1. Requisitos a instalar en la máquina nueva

Instalar en este orden, todo con el instalador oficial (next, next, finish):

1. **Git** → https://git-scm.com/download/win
2. **Node.js** (versión LTS) → https://nodejs.org/
3. **WAMP Server 64 bits** → https://www.wampserver.com/
   - Durante la instalación, cuando pida las versiones de PHP/MySQL a
     incluir, dejar las que vienen por defecto; esto se puede ajustar
     después si el proyecto exige otra versión de PHP (ver sección "Dos
     versiones de PHP" en `CLAUDE.md` si aplica).

Verificar que todo quedó instalado abriendo una terminal (`cmd` o
PowerShell) y corriendo:

```
git --version
node -v
php -v
```

## 2. Clonar el proyecto

Dentro de la carpeta `www` de WAMP (normalmente `C:\wamp64\www\`):

```
cd C:\wamp64\www
git clone <url-del-repositorio> app_far
cd app_far
composer install
npm install
npm run build
copy .env.example .env
php artisan key:generate
```

Editar `.env` y completar los datos de conexión a la base de datos
(`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y dejar:

```
APP_URL=http://farmafamilia.local
```

Importar la base de datos (desde phpMyAdmin o línea de comandos) con el
respaldo más reciente de `storage/backups/` o el dump correspondiente.

Luego:

```
php artisan migrate
php artisan pharmacy:upgrade-schema
```

## 3. Crear el dominio local `farmafamilia.local`

**No editar `httpd-vhosts.conf` ni el archivo `hosts` a mano** — WAMP trae su
propia herramienta que hace ambas cosas de forma segura:

1. Click derecho en el icono de WAMP en la bandeja del sistema (debe estar
   en verde) → **Apache** → **Add a Virtual Host**.
   - Si no aparece esa opción, abrir directamente en el navegador:
     `http://localhost/add_vhost.php`
2. Completar el formulario:
   - **Server Name**: `farmafamilia.local`
   - **Directory of the Vhost**: `app_far/public`
     (o la ruta completa `c:/wamp64/www/app_far/public`)
3. Guardar. WAMP reinicia Apache solo y agrega la entrada al archivo
   `hosts` automáticamente.
4. Probar abriendo `http://farmafamilia.local/login` en el navegador — debe
   cargar el sistema con sus estilos normales.

> Si por algún motivo `add_vhost.php` no agrega la entrada al `hosts`
> (poco común), hay que agregarla manualmente abriendo el Bloc de notas
> **como Administrador** y editando
> `C:\Windows\System32\drivers\etc\hosts`, agregando la línea:
> `127.0.0.1    farmafamilia.local`

## 4. Hacer que Apache y MySQL arranquen solos con Windows

Por defecto los servicios de WAMP (`wampapache64`, `wampmysqld64`) existen
pero no arrancan automáticamente al prender la máquina. Para activarlo
(requiere una sesión de PowerShell o `services.msc` como Administrador):

**Opción A — PowerShell como Administrador:**

```powershell
Set-Service -Name wampapache64 -StartupType Automatic
Set-Service -Name wampmysqld64 -StartupType Automatic
Start-Service wampapache64
Start-Service wampmysqld64
```

**Opción B — Interfaz gráfica:**

1. Abrir `services.msc` (Windows + R, escribir `services.msc`, Enter).
2. Buscar **wampapache64**, doble click, en "Tipo de inicio" elegir
   **Automático**, Aplicar.
3. Repetir lo mismo para **wampmysqld64**.

Con esto, aunque nadie inicie sesión en Windows, Apache y MySQL ya estarán
corriendo y el sistema estará disponible en la red.

## 5. Acceso directo que abre el sistema automáticamente

Para que, además de los servicios, se abra el navegador ya apuntando al
sistema apenas alguien inicie sesión en Windows:

1. Clic derecho en el Escritorio → **Nuevo → Acceso directo**.
2. En la ubicación del elemento, pegar (ajustando la ruta de Chrome si está
   instalado en otro lado):

   ```
   "C:\Program Files\Google\Chrome\Application\chrome.exe" --app=http://farmafamilia.local
   ```

3. Nombrarlo **Farmacia FarmaFamilia** y finalizar.
4. (Opcional, para que la sugerencia sea el ícono del sistema) clic derecho
   sobre el acceso directo → Propiedades → Cambiar icono → buscar
   `C:\wamp64\www\app_far\public\favicon.ico`.

Esto abre Chrome en modo "app" (sin barra de direcciones ni pestañas), dando
la sensación de una aplicación de escritorio real.

### Para que se abra solo al encender la máquina

1. Copiar ese mismo acceso directo (Ctrl+C).
2. Presionar Windows + R, escribir `shell:startup` y Enter — se abre la
   carpeta de inicio de Windows para el usuario actual.
3. Pegar el acceso directo copiado ahí (Ctrl+V).

Con esto, cada vez que ese usuario de Windows inicie sesión, se abrirá
automáticamente el navegador con el sistema cargado.

> Nota: los servicios de WAMP (paso 4) arrancan sin necesidad de que nadie
> inicie sesión. El acceso directo de esta sección sí depende de que algún
> usuario inicie sesión en Windows (lo normal en una compu de mostrador que
> se deja con sesión abierta).

## 6. Credenciales de acceso al sistema

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `maicolarteaga0711@gmail.com` | `Admin123!` |
| Trabajador | `trabajador@farmafamilia.com` | `63568888` |
| Trabajador 2 | `trabajador2@farmafamilia.com` | `63568888` |

> Recomendado: cambiar estas contraseñas desde **Usuarios → Editar** una vez
> el sistema esté en producción, sobre todo si más de una persona conoce la
> contraseña por defecto.

## 7. Verificación final

- [ ] `http://farmafamilia.local/login` carga con estilos (si se ve sin
      estilos, revisar que no exista el archivo `public/hot` — si existe,
      borrarlo; significa que quedó un `npm run dev` corriendo antes de
      compilar para producción con `npm run build`).
- [ ] Login de administrador funciona y ve todos los módulos del menú.
- [ ] Login de trabajador funciona y solo ve Inicio, Caja y Ventas.
- [ ] Reiniciar la máquina completa y confirmar que, sin tocar nada:
  - WAMP queda en verde (servicios corriendo).
  - El navegador se abre solo con el sistema cargado.
