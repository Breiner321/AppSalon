# Manual de usuario (T8)

- `manual-usuario.html`: fuente del manual. HTML autónomo con CSS de impresión (A4, márgenes de 2 cm,
  una sección por página). Usa las capturas de `docs/capturas/` (ver `docs/capturas/LISTA.md`).
- `Manual-de-usuario-AppSalon.pdf`: el entregable. **5 páginas** (máximo permitido: 5).

## Regenerar el PDF

En PowerShell, desde la raíz del proyecto:

```powershell
& "C:\Program Files\Google\Chrome\Application\chrome.exe" --headless --print-to-pdf="C:\Users\Lunal\Repositorios\AppSalon\docs\manual\Manual-de-usuario-AppSalon.pdf" --no-pdf-header-footer "file:///C:\Users\Lunal\Repositorios\AppSalon/docs/manual/manual-usuario.html"
php docs/manual/contar-paginas.php
```

Si Chrome no está en esa ruta, usa `C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe` con las mismas opciones.

Si cambias las capturas o el texto, abre el HTML en el navegador: cada hoja se ve como un rectángulo blanco de A4.
Si una sección se alarga más que su hoja, el PDF pasará de 5 páginas y `contar-paginas.php` terminará con error.
