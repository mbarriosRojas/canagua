# Instrucciones Rapidas de Instalacion - SACRGAPI

## Para usuarios SIN conocimientos tecnicos (Windows + XAMPP)

---

### Requisito unico: XAMPP

Descargue e instale XAMPP desde: **https://www.apachefriends.org/es/download.html**

---

### Paso 1: Preparar XAMPP

1. Abra el **Panel de Control de XAMPP**
2. Haga clic en **[Start]** junto a **Apache**
3. Haga clic en **[Start]** junto a **MySQL**
4. Ambos deben aparecer en verde con el texto **"Running"**

---

### Paso 2: Instalar el Sistema

1. Copie la carpeta **SACRGAPI** dentro de: `C:\xampp\htdocs\`
   - Resultado: `C:\xampp\htdocs\SACRGAPI\`
2. Abra la carpeta `C:\xampp\htdocs\SACRGAPI\`
3. Haga **doble clic** en el archivo **`install.bat`**
4. Siga las instrucciones en pantalla

---

### Paso 3: Usar el Sistema

1. Abra su navegador (Chrome, Firefox, Edge)
2. Vaya a: **http://localhost/SACRGAPI/public/**
3. Inicie sesion con:
   - **Usuario**: admin
   - **Contrasena**: admin123

---

## URLs importantes

| Recurso | URL |
|---|---|
| Sistema principal | http://localhost/SACRGAPI/public/ |
| phpMyAdmin (BD) | http://localhost/phpmyadmin |

---

## Si algo no funciona

1. Verifique que Apache y MySQL esten **Running** en el Panel de XAMPP
2. Verifique que la carpeta este en `C:\xampp\htdocs\SACRGAPI\`
3. Abra http://localhost/SACRGAPI/public/test_connection.php para diagnosticar

---

**Consulte INSTRUCCIONES_INSTALACION.md para instrucciones detalladas.**
