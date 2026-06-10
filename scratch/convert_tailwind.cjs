const fs = require('fs');

const mappings = {
    'primary': '#0050cb',
    'primary-container': '#0066ff',
    'on-primary-container': '#f8f7ff',
    'secondary': '#006a61',
    'secondary-container': '#86f2e4',
    'on-secondary-container': '#006f66',
    'tertiary': '#4345d1',
    'tertiary-container': '#5d60eb',
    'on-tertiary-container': '#faf6ff',
    'error': '#ba1a1a',
    'error-container': '#ffdad6',
    'on-error-container': '#93000a',
    'surface': '#fcf9f8',
    'on-surface': '#1c1b1b',
    'surface-variant': '#e5e2e1',
    'on-surface-variant': '#424656',
    'surface-container-low': '#f6f3f2',
    'surface-container': '#f0eded',
    'surface-container-high': '#eae7e7',
    'surface-container-highest': '#e5e2e1',
    'outline': '#727687',
    'outline-variant': '#c2c6d8',
    'primary-fixed': '#dae1ff',
    'primary-fixed-dim': '#b3c5ff',
    'on-primary-fixed': '#001849',
    'on-primary-fixed-variant': '#003fa4',
};

const spacingMappings = {
    'xs': '8px',
    'sm': '12px',
    'md': '16px',
    'lg': '24px',
    'xl': '32px',
    '2xl': '48px',
    '3xl': '64px',
    'base': '4px',
    'gutter': '24px',
    'margin-desktop': '40px',
    'margin-mobile': '16px',
};

function convert(inputFile, outputFile) {
    let html = fs.readFileSync(inputFile, 'utf8');

    // Extract just the <main> or <div class="flex"> content if needed
    // But let's just do text replacements for now.
    
    // Replace Colors (text-, bg-, border-, ring-, fill-, stroke-)
    for (const [key, value] of Object.entries(mappings)) {
        const regexText = new RegExp(`\\btext-${key}\\b`, 'g');
        const regexBg = new RegExp(`\\bbg-${key}\\b`, 'g');
        const regexBorder = new RegExp(`\\bborder-${key}\\b`, 'g');
        const regexBorderL = new RegExp(`\\bborder-l-${key}\\b`, 'g');
        const regexRing = new RegExp(`\\bring-${key}\\b`, 'g');
        
        // Handle opacity like bg-primary/10 -> bg-[#0050cb]/10
        const regexBgOp = new RegExp(`\\bbg-${key}/(\\d+)\\b`, 'g');
        const regexTextOp = new RegExp(`\\btext-${key}/(\\d+)\\b`, 'g');
        const regexBorderOp = new RegExp(`\\bborder-${key}/(\\d+)\\b`, 'g');
        
        html = html.replace(regexBgOp, `bg-[${value}]/$1`);
        html = html.replace(regexTextOp, `text-[${value}]/$1`);
        html = html.replace(regexBorderOp, `border-[${value}]/$1`);
        
        html = html.replace(regexText, `text-[${value}]`);
        html = html.replace(regexBg, `bg-[${value}]`);
        html = html.replace(regexBorder, `border-[${value}]`);
        html = html.replace(regexBorderL, `border-l-[${value}]`);
        html = html.replace(regexRing, `ring-[${value}]`);
    }

    // Replace Spacing (p-, px-, py-, pt-, pb-, m-, mx-, my-, mt-, mb-, gap-)
    for (const [key, value] of Object.entries(spacingMappings)) {
        const prefixes = ['p', 'px', 'py', 'pt', 'pb', 'pl', 'pr', 'm', 'mx', 'my', 'mt', 'mb', 'ml', 'mr', 'gap'];
        for (const p of prefixes) {
            const regex = new RegExp(`\\b${p}-${key}\\b`, 'g');
            html = html.replace(regex, `${p}-[${value}]`);
        }
    }

    // Replace Fonts (font-headline-md, text-headline-md, etc.)
    html = html.replace(/\bfont-headline-lg\b/g, "font-['Inter'] font-semibold");
    html = html.replace(/\btext-headline-lg\b/g, "text-[32px] leading-[40px] tracking-[-0.03em]");
    
    html = html.replace(/\bfont-headline-md\b/g, "font-['Inter'] font-semibold");
    html = html.replace(/\btext-headline-md\b/g, "text-[20px] leading-[28px] tracking-[-0.02em]");
    
    html = html.replace(/\bfont-body-lg\b/g, "font-['Inter']");
    html = html.replace(/\btext-body-lg\b/g, "text-[16px] leading-[26px]");
    
    html = html.replace(/\bfont-body-md\b/g, "font-['Inter']");
    html = html.replace(/\btext-body-md\b/g, "text-[14px] leading-[22px]");
    
    html = html.replace(/\bfont-label-md\b/g, "font-['Geist'] font-medium");
    html = html.replace(/\btext-label-md\b/g, "text-[12px] leading-[16px] tracking-[0.02em]");
    
    html = html.replace(/\bfont-display-lg\b/g, "font-['Inter'] font-bold");
    html = html.replace(/\btext-display-lg\b/g, "text-[48px] leading-[56px] tracking-[-0.04em]");
    
    html = html.replace(/\bfont-mono-sm\b/g, "font-['Geist']");
    html = html.replace(/\btext-mono-sm\b/g, "text-[12px] leading-[18px]");

    // Extract the <main> block
    const mainMatch = html.match(/<main[\s\S]*?<\/main>/);
    let outputContent = html;
    
    if (mainMatch) {
        outputContent = mainMatch[0];
        // Strip out max-w-7xl mx-auto if it's there so we can wrap it
        outputContent = outputContent.replace('max-w-7xl mx-auto', '');
    }
    
    // Add dark mode considerations for specific backgrounds?
    // We can do that manually in the blade files.

    fs.writeFileSync(outputFile, outputContent);
    console.log(`Converted ${inputFile} -> ${outputFile}`);
}

const args = process.argv.slice(2);
if (args.length === 2) {
    convert(args[0], args[1]);
} else {
    console.log("Usage: node convert_tailwind.js <input.html> <output.html>");
}
