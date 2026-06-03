; Julius Fitness Gym — Windows installer (Inno Setup 6)
; Build: run installer\build-installer.bat on Windows (requires Inno Setup + Node)

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
; Copiază tot proiectul mai puțin vendor/node_modules/.git
Source: "..\*"; DestDir: "{app}"; \
  Flags: recursesubdirs createallsubdirs; \
  Excludes: "vendor\*,node_modules\*,.git\*,storage\logs\*,storage\framework\cache\*,storage\framework\sessions\*,storage\framework\views\*,.env,dist\*,installer\*"

; Scripturi necesare la rularea install.bat (folderul installer e exclus mai sus)
Source: "..\installer\check-prerequisites.bat"; DestDir: "{app}\installer"; Flags: ignoreversion

[Run]
; Rulează install.bat după copiere
Filename: "{app}\install.bat"; \
  Description: "Configurează aplicația"; \
  Flags: runhidden waituntilterminated postinstall

; Deschide browserul după instalare
Filename: "{app}\open.bat"; \
  Description: "Deschide Julius Fitness Gym în browser"; \
  Flags: postinstall skipifsilent

[Icons]
; Shortcut pe Desktop
Name: "{userdesktop}\Julius Fitness Gym"; \
  Filename: "{app}\open.bat"; \
  IconFilename: "{app}\public\favicon.ico"; \
  Comment: "Deschide Julius Fitness Gym"

; Shortcut în Start Menu
Name: "{userprograms}\Julius Fitness Gym\Julius Fitness Gym"; \
  Filename: "{app}\open.bat"
Name: "{userprograms}\Julius Fitness Gym\Dezinstalare"; \
  Filename: "{uninstallexe}"

[Messages]
WelcomeLabel1=Bun venit la instalarea Julius Fitness Gym
WelcomeLabel2=Acest program va instala Julius Fitness Gym versiunea 1.0 pe calculatorul dumneavoastră.%n%nAsigurați-vă că Laravel Herd este instalat înainte de a continua.%n%nApăsați Înainte pentru a continua.
FinishedLabel=Julius Fitness Gym a fost instalat cu succes!%n%nAplicația se va deschide automat în browser la adresa:%n%nhttp://julius-fitness-gym.test%n%nDate autentificare:%nAdmin: admin@julius.test%nParolă: julius2024
