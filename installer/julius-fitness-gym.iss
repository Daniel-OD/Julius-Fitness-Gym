; Julius Fitness Gym — Windows installer (Inno Setup 6)
; Build: installer\build-installer.bat (include vendor + assets compilate)

[Setup]
AppName=Julius Fitness Gym
AppVersion=1.0
AppPublisher=Julius Fitness Gym
DefaultDirName={userdocs}\..\Herd\julius-fitness-gym
DisableDirPage=yes
OutputBaseFilename=Julius-Fitness-Gym-Setup-v1.0
OutputDir=../dist
SetupIconFile=../public/favicon.ico
WizardStyle=modern
Compression=lzma2/ultra64
SolidCompression=yes
PrivilegesRequired=lowest

[Languages]
Name: "romanian"; MessagesFile: "compiler:Languages\Romanian.isl"

[Files]
; Proiect cu vendor si build incluse — fara node_modules / .git
Source: "..\*"; DestDir: "{app}"; \
  Flags: recursesubdirs createallsubdirs; \
  Excludes: "node_modules\*,.git\*,storage\logs\*,storage\framework\cache\*,storage\framework\sessions\*,storage\framework\views\*,storage\app\install-credentials.txt,storage\app\.install-complete,.env,dist\*,installer\build-*,installer\julius-fitness-gym.iss,installer\mac-app\*"

Source: "..\installer\check-prerequisites.bat"; DestDir: "{app}\installer"; Flags: ignoreversion
Source: "..\installer\check-prerequisites.sh"; DestDir: "{app}\installer"; Flags: ignoreversion
Source: "..\installer\post-install.bat"; DestDir: "{app}\installer"; Flags: ignoreversion
Source: "..\installer\post-install.sh"; DestDir: "{app}\installer"; Flags: ignoreversion

[Run]
Filename: "{app}\install.bat"; \
  Description: "Configurează aplicația (migrări, admin, Herd)"; \
  Flags: runhidden waituntilterminated postinstall

Filename: "{app}\open.bat"; \
  Description: "Deschide panoul de administrare"; \
  Flags: postinstall skipifsilent nowait

[Icons]
Name: "{userdesktop}\Julius Fitness Gym"; \
  Filename: "{app}\open.bat"; \
  IconFilename: "{app}\public\favicon.ico"; \
  WorkingDir: "{app}"; \
  Comment: "Julius Fitness Gym — Administrare"

Name: "{userprograms}\Julius Fitness Gym\Julius Fitness Gym"; \
  Filename: "{app}\open.bat"; \
  WorkingDir: "{app}"
Name: "{userprograms}\Julius Fitness Gym\Instalare"; \
  Filename: "{app}\install.bat"; \
  WorkingDir: "{app}"
Name: "{userprograms}\Julius Fitness Gym\Dezinstalare"; \
  Filename: "{uninstallexe}"

[Messages]
WelcomeLabel1=Bun venit la instalarea Julius Fitness Gym
WelcomeLabel2=Acest program instalează Julius Fitness Gym v1.0.%n%nNecesită Laravel Herd pentru Windows (Composer și Node sunt incluse în pachet).%n%nApăsați Înainte pentru a continua.
FinishedLabel=Instalare finalizată!%n%nSite: http://julius-fitness-gym.test%nAdmin: http://julius-fitness-gym.test/admin%n%nCredențiale implicite:%nEmail: admin@julius.test%nParolă: julius2024%n%nSalvate și în storage\app\install-credentials.txt%n%nShortcut pe Desktop creat.
