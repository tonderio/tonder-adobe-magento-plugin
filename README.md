##  Manual de Instalación de un Plugin de Tonder en Magento desde la Terminal

- Paso 1: Acceso a la Terminal
Abre una ventana de terminal en tu servidor donde esté instalado Magento.
Accede al directorio raíz de tu instalación de Magento utilizando el comando
> cd /ruta/a/tu/instalacion/magento

- Paso 2: Descarga del Plugin
Utiliza Composer para instalar el plugin. Ejecuta el siguiente comando para descargar el plugin:
> composer require tonder/module-payment dev-m2.4.2

- Paso 3: Habilitar el Plugin
Una vez que la descarga se haya completado, ejecuta el siguiente comando para habilitar el plugin:

> bin/magento module:enable Tonder_Payment --clear-static-content

- Paso 4: Actualización del Sistema
Después de habilitar el plugin, ejecuta el siguiente comando para realizar las actualizaciones necesarias en el sistema:

> bin/magento setup:upgrade

- Paso 6: Ejecuta el siguiente comando para compilar las clases de inyección de dependencia:.

> bin/magento setup:di:compile

- Paso 7: Limpieza de la Caché
Para asegurarte de que los cambios se reflejen correctamente, ejecuta el siguiente comando para limpiar la caché:

> php bin/magento cache:flush 

- Paso 8: Configuración
Accede a la consola de administración de Magento para configurar el plugin.(Vea el documento proporcionado por el proveedor)
