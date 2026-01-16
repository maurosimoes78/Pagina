# Favicon do Projeto

## Como adicionar o favicon

1. **Preparar o ícone:**
   - Formato recomendado: ICO, PNG ou SVG
   - Tamanhos recomendados:
     - 16x16 pixels (favicon.ico)
     - 32x32 pixels
     - 192x192 pixels (Android)
     - 512x512 pixels (iOS/Android)

2. **Converter para ICO (opcional):**
   - Use ferramentas online como https://favicon.io/ ou https://www.favicon-generator.org/
   - Ou use ferramentas como ImageMagick

3. **Substituir o arquivo:**
   - Substitua o arquivo `public/favicon.ico` pelo seu novo ícone
   - Mantenha o nome `favicon.ico`

4. **Para múltiplos tamanhos (recomendado):**
   - Coloque os arquivos na pasta `public/`:
     - `favicon-16x16.png`
     - `favicon-32x32.png`
     - `android-chrome-192x192.png`
     - `android-chrome-512x512.png`
     - `apple-touch-icon.png` (180x180)
   - O `index.html` já está configurado para usar esses arquivos

## Nota
O favicon também é usado como logo no header do site. Certifique-se de que o ícone seja legível em tamanhos pequenos.

