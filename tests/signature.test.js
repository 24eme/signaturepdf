const puppeteer = require('puppeteer');
const cp = require("child_process");
var headless = true;
if(process.env.DEBUG) {
    headless = false;
}
var page = null;
var browser = null;
var server = null
var host = "localhost:"+(9000 + Math.floor((Math.random() * 1000)));

describe("Signature d'un pdf", () => {
    var originX;
    var originY;
    var hash;
    beforeAll(async () => {
        server = cp.spawn("php", ["-S", host, "-t", "public"]);
        browser = await puppeteer.launch({ headless: headless });
        page = await browser.newPage();
        await page.setViewport({ width: 1200, height: 700 })
        await page.goto('http://' + host + '/');
    });
    it('Upload et chargement du pdf', async () => {
        await (await page.$("input#input_pdf_upload")).uploadFile(require('path').resolve(__dirname + '/files/document.pdf'));
        await page.waitForNavigation()
        await page.waitForSelector('#canvas-pdf-15', {visible: true});
        expect(await page.evaluate(() => { return document.querySelectorAll('.canvas-pdf').length })).toBe(16);
        hash = await page.url().replace(/^.+\//, '');
    });
    it("Création d'une signature", async () => {
        await page.waitForSelector('#label_svg_signature_add', {visible: true});
        await page.waitForTimeout(300);
        await page.click("#label_svg_signature_add")
        await page.waitForSelector('#signature-pad', {visible: true});
        await page.waitForTimeout(200);
        await page.mouse.move(600,150);
        await page.mouse.down();
        await page.mouse.move(700,250, {steps: 20});
        await page.mouse.up();
        await page.mouse.move(600,250);
        await page.mouse.down();
        await page.mouse.move(700,150,{steps: 20});
        await page.mouse.up();
        await page.waitForSelector('button#btn_modal_ajouter:not([disabled])');
        await page.waitForTimeout(100);
        await page.click('button#btn_modal_ajouter');
        await page.waitForTimeout(300);
        expect(await page.evaluate(() => { return document.querySelector("#label_svg_0 img").src })).toMatch(/^data:image\/svg\+xml;base64,.+/);
    });
    it('Ajout de la signature dans le pdf', async () => {
        originX = await page.evaluate(() => { return document.querySelector("#canvas-container-0").offsetLeft; });
        originY = await page.evaluate(() => { return document.querySelector("#canvas-container-0").offsetTop; });
        await page.mouse.click(originX + 50, originY + 50);
        await page.waitForTimeout(300);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(1);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].getScaledHeight())})).toBe(100);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].getScaledWidth())})).toBe(100);
        expect(await page.evaluate(() => { return Math.abs(Math.round(canvasEditions[0].getObjects()[0].left))})).toBe(0);
        expect(await page.evaluate(() => { return Math.abs(Math.round(canvasEditions[0].getObjects()[0].top))})).toBe(0);
    });
    it('Déplacement de la signature', async () => {
        await page.mouse.down();
        await page.waitForTimeout(100);
        await page.mouse.move(originX + 350, originY + 350);
        await page.mouse.up();
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].left)})).toBe(300);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].top)})).toBe(300);
        await page.waitForTimeout(100);
    });
    it('Redimensionnement de la signature', async () => {
        await page.mouse.move(originX + 400, originY + 400);
        await page.mouse.down();
        await page.waitForTimeout(100);
        await page.mouse.move(originX + 450, originY + 450);
        await page.mouse.up();
        await page.waitForTimeout(100);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].getScaledHeight())})).toBe(150);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].getScaledWidth())})).toBe(150);
    });
    it("Ajout d'une seconde signature", async () => {
        await page.click("#label_svg_0");
        expect(await page.evaluate(() => { return document.body.style.cursor; })).toBe("copy");
        expect(await page.evaluate(() => { return document.querySelector('#label_svg_0').style.cursor; })).toBe("copy");
        expect(await page.evaluate(() => { return canvasEditions[0].defaultCursor; })).toBe('copy');
        await page.waitForTimeout(100);
        await page.mouse.click(originX + 50, originY + 50);
        expect(await page.evaluate(() => { return document.body.style.cursor; })).toBe("");
        expect(await page.evaluate(() => { return document.querySelector('#label_svg_0').style.cursor; })).toBe("");
        expect(await page.evaluate(() => { return canvasEditions[0].defaultCursor; })).toBe('default');
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(2);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[1].getScaledHeight())})).toBe(150);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[1].getScaledWidth())})).toBe(150);
    });
    it("Suppression de la seconde signature du pdf", async () => {
        await page.mouse.click(originX + 50, originY + 50);
        await page.waitForTimeout(100);
        await page.keyboard.press('Delete');
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(1);
    })
    it("Suppression de la signature de la liste", async () => {
        await page.click("#label_svg_0 .btn-svg-list-suppression")
        await page.waitForTimeout(100);
        expect(await page.evaluate(() => { return document.querySelector("#label_svg_0 img") })).toBeNull();
    });
    it("Téléchargement du pdf signé", async () => {
        await page._client.send('Page.setDownloadBehavior', {behavior: 'allow', downloadPath: './tests/downloads'});
        await page.click("#save");
        await page.waitForTimeout(500);
        await expect(require('fs').existsSync('./tests/downloads/'+hash+'_signe.pdf')).toBe(true);
    });
    afterAll(async () => {
        if(process.env.DEBUG) {
            return;
        }
        await require('fs').unlinkSync('./tests/downloads/'+hash+'_signe.pdf');
        await server.kill();
        await browser.close();
    });
});
