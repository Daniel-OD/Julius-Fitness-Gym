# Installer Windows — Julius Fitness Gym

## Cerințe (pe mașina de build)

- [Inno Setup 6](https://jrsoftware.org/isinfo.php)
- Node.js (pentru `npm run build` înainte de packaging)
- `public/favicon.ico` — necesar pentru iconița installerului (convertește din `public/favicon.svg` dacă lipsește)

## Generare `.exe`

1. Deschide Command Prompt în folderul proiectului.
2. Rulează:

```bat
installer\build-installer.bat
```

3. Installerul apare în `dist/Julius-Fitness-Gym-Setup-v1.0.exe`.

## Instalare pe PC client

1. Instalează [Laravel Herd pentru Windows](https://herd.laravel.com/windows), Composer și Node.js.
2. Rulează `Julius-Fitness-Gym-Setup-v1.0.exe`.
3. Fișierele se copiază în `%USERPROFILE%\Herd\julius-fitness-gym`.
4. La final rulează automat `install.bat` (dependențe + migrări + build).

## După instalare

- Deschide aplicația: shortcut **Julius Fitness Gym** sau `open.bat`
- URL: http://julius-fitness-gym.test
- Asigură-te că site-ul este link-uit în Herd (același nume de folder).
