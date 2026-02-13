import fs from "node:fs";
import path from "node:path";

const repoRoot = path.resolve(process.cwd());

const readText = (p) => fs.readFileSync(p, "utf8");

const extractObjectLiteral = (source, marker) => {
  const idx = source.indexOf(marker);
  if (idx < 0) throw new Error(`Marker not found: ${marker}`);
  const braceStart = source.indexOf("{", idx);
  if (braceStart < 0) throw new Error("Opening brace not found");

  let depth = 0;
  for (let i = braceStart; i < source.length; i += 1) {
    const ch = source[i];
    if (ch === "{") depth += 1;
    if (ch === "}") depth -= 1;
    if (depth === 0) {
      return source.slice(braceStart, i + 1);
    }
  }
  throw new Error("Unbalanced braces while extracting object literal");
};

const evalObject = (expr, context = {}) => {
  const keys = Object.keys(context);
  const values = Object.values(context);
  // eslint-disable-next-line no-new-func
  const fn = new Function(...keys, `return (${expr});`);
  return fn(...values);
};

const pageTextBlocksPath = path.join(repoRoot, "app/utils/pageTextBlocks.ts");
const contactSettingsPath = path.join(repoRoot, "app/utils/contactSettings.ts");

const pageTextBlocksSrc = readText(pageTextBlocksPath);
const contactSettingsSrc = readText(contactSettingsPath);

const pageBlocksExpr = extractObjectLiteral(pageTextBlocksSrc, "export const DEFAULT_PAGE_BLOCKS");
const pageBlocks = evalObject(pageBlocksExpr);

const defaultsExpr = extractObjectLiteral(contactSettingsSrc, "export const DEFAULT_CONTACT_SETTINGS");
const DEFAULT_CONTACT_SETTINGS = evalObject(defaultsExpr, { DEFAULT_PAGE_BLOCKS: pageBlocks });

const outDir = path.join(repoRoot, "PHP_logush/storage/defaults");
fs.mkdirSync(outDir, { recursive: true });
const outPath = path.join(outDir, "settings.json");
fs.writeFileSync(outPath, JSON.stringify(DEFAULT_CONTACT_SETTINGS, null, 2) + "\n", "utf8");

process.stdout.write(`Wrote ${outPath}\n`);

