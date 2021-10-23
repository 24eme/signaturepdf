const puppeteer = require('puppeteer');
const cp = require("child_process");
var headless = true;
if(process.env.SHOW) {
    headless = false;
}
var page = null;
var browser = null;
var server = null
var host = "localhost:"+(9000 + Math.floor((Math.random() * 1000)));

describe("Signature d'un pdf", () => {
    beforeAll(async () => {
        server = cp.spawn("php", ["-S", host, "-t", "public"]);
        browser = await puppeteer.launch({ headless: headless });
        page = await browser.newPage();
        await page.setViewport({ width: 1200, height: 800 })
        await page.goto('http://' + host + '/');
    });
    it('Upload et chargement du pdf', async () => {
        await (await page.$("input#input_pdf_upload")).uploadFile(require('path').resolve(__dirname + '/files/document.pdf'));
        await page.waitForNavigation()
    });
    it("Création d'une signature", async () => {
        await page.waitForSelector('#label_svg_signature_add', {visible: true});
        await page.waitForTimeout(300);
        await page.click("#label_svg_signature_add")
        await page.waitForSelector('#signature-pad', {visible: true});
        await page.waitForTimeout(100);
        await page.click('#signature-pad');
        await page.waitForSelector('button#btn_modal_ajouter:not([disabled])');
        await page.waitForTimeout(100);
        await page.click('button#btn_modal_ajouter');
        await page.waitForTimeout(300);
    });
    it('Ajout de la signature dans le pdf', async () => {
        await page.mouse.click(100,100);
        await page.waitForTimeout(100);
    });
    it('Déplacement de la signature', async () => {
        await page.mouse.down();
        await page.waitForTimeout(100);
        await page.mouse.move(400,400);
        await page.mouse.up();
        await page.waitForTimeout(100);
  // Redimensionnement de la signature
  await page.mouse.move(460,450);
  await page.mouse.down();
  await page.waitForTimeout(100);
  await page.mouse.move(500,500);
  await page.mouse.up();
  await page.waitForTimeout(100);
  // Ajout d'une seconde signature
  await page.click("#label_svg_0");
  await page.waitForTimeout(100);
  await page.mouse.click(100,100);
  // Suppression de la seconde signature
  await page.mouse.click(100,100);
  await page.waitForTimeout(100);
  await page.keyboard.press('Delete');
  // Suppression de la signature de la liste
  await page.click("#label_svg_0 .btn-svg-list-suppression")
  await page.waitForTimeout(100);
  });
  afterAll(async () => {
  await browser.close();
  await server.kill();
    });
});









