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
        await page.waitForTimeout(300);
    });
    it("Création d'une signature", async () => {
        await page.waitForSelector('#label_svg_signature_add', {visible: true});
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
    it('Ajout de la signature au pdf', async () => {
        originX = await page.evaluate(() => { return document.querySelector("#canvas-container-0").offsetLeft; });
        originY = await page.evaluate(() => { return document.querySelector("#canvas-container-0").offsetTop; });
        await page.mouse.click(originX + 50, originY + 50);
        await page.waitForTimeout(300);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(1);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].getScaledHeight())})).toBe(100);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[0].getScaledWidth())})).toBe(100);
        expect(await page.evaluate(() => { return Math.abs(Math.round(canvasEditions[0].getObjects()[0].left))})).toBe(0);
        expect(await page.evaluate(() => { return Math.abs(Math.round(canvasEditions[0].getObjects()[0].top))})).toBe(0);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_0').checked; })).toBe(true);
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
    it("Ajout d'une seconde signature : conservation de la dernière largeur et curseur", async () => {
        await page.mouse.click(originX + 200, originY + 200);
        await page.waitForTimeout(100);
        //expect(await page.evaluate(() => { return document.body.style.cursor; })).toBe("copy");
        //expect(await page.evaluate(() => { return document.querySelector('#label_svg_0').style.cursor; })).toBe("copy");
        expect(await page.evaluate(() => { return canvasEditions[0].defaultCursor; })).toBe('copy');
        await page.waitForTimeout(100);
        await page.mouse.click(originX + 50, originY + 50);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_0').checked; })).toBe(true);
        expect(await page.evaluate(() => { return canvasEditions[0].defaultCursor; })).toBe('copy');
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(2);
        await page.mouse.click(originX + 50, originY + 50);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(2);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[1].getScaledHeight())})).toBe(150);
        expect(await page.evaluate(() => { return Math.round(canvasEditions[0].getObjects()[1].getScaledWidth())})).toBe(150);
    });
    it("Suppression de la seconde signature du pdf", async () => {
        await page.mouse.click(originX + 50, originY + 50);
        await page.keyboard.press('Delete');
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(1);
    })
    it("Création d'une paraphe", async () => {
        await page.click("#label_svg_initials_add");
        await page.waitForSelector('#input-text-signature', {visible: true});
        await page.type("#input-text-signature", "FSF");
        await page.waitForSelector('button#btn_modal_ajouter:not([disabled])');
        await page.waitForTimeout(300);
        await page.click('button#btn_modal_ajouter');
        await page.waitForTimeout(300);
        expect(await page.evaluate(() => { return document.querySelector("#label_svg_1 img").src })).toMatch(/^data:image\/svg\+xml;base64,.+/);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_1').checked; })).toBe(true);
        await page.click("#label_svg_1");
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_1').checked; })).toBe(false);
    })
    it("Ajout de la paraphe au pdf", async () => {
        await page.click("#label_svg_1");
        await page.mouse.click(originX + 700, originY + 600);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(2);
    });
    it("Création d'un tampon", async () => {
        await page.click("#label_svg_rubber_stamber_add");
        await page.waitForSelector('#input-image-upload', {visible: true});
        await (await page.$("input#input-image-upload")).uploadFile(require('path').resolve(__dirname + '/files/tampon.png'));
        await page.waitForSelector('button#btn_modal_ajouter:not([disabled])');
        await page.waitForTimeout(300);
        await page.click('button#btn_modal_ajouter');
        await page.waitForTimeout(300);
        expect(await page.evaluate(() => { return document.querySelector("#label_svg_2 img").src })).toMatch(/^data:image\/svg\+xml;base64,.+/);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_2').checked; })).toBe(true);
        await page.click("#label_svg_2");
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_2').checked; })).toBe(false);
    })
    it("Ajout du tampon au pdf", async () => {
        await page.click("#label_svg_2");
        await page.mouse.click(originX + 650, originY + 375);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(3);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_2').checked; })).toBe(true);
    });
    it("Création d'une signature à partir d'une image", async () => {
        await page.click("#btn-add-svg");
        await page.waitForSelector('#nav-import-tab', {visible: true});
        await page.click("#nav-import-tab");
        await page.waitForSelector('#input-image-upload', {visible: true});
        await (await page.$("input#input-image-upload")).uploadFile(require('path').resolve(__dirname + '/files/signature.png'));
        await page.waitForSelector('button#btn_modal_ajouter:not([disabled])');
        await page.waitForTimeout(300);
        await page.click('button#btn_modal_ajouter');
        await page.waitForTimeout(300);
        expect(await page.evaluate(() => { return document.querySelector("#label_svg_3 img").src })).toMatch(/^data:image\/svg\+xml;base64,.+/);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_3').checked; })).toBe(true);
    });
    it("Ajout de la signature au pdf", async () => {
        await page.mouse.click(originX + 400, originY + 600);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(4);
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_3').checked; })).toBe(true);
    });
    it("Ajout de texte au pdf", async () => {
        await page.click("#label_svg_text");
        await page.mouse.click(originX + 150, originY + 100);
        await page.keyboard.type('Bon pour un logiciel libre épatant !');
        await page.mouse.click(originX + 150, originY + 50);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects().length; })).toBe(5);
        expect(await page.evaluate(() => { return canvasEditions[0].getObjects()[4].text; })).toBe('Bon pour un logiciel libre épatant !');
        expect(await page.evaluate(() => { return document.querySelector('#radio_svg_text').checked; })).toBe(true);
    });
    it("Suppression de tous les éléments ajoutés à la liste", async () => {
        await page.click("#label_svg_0 .btn-svg-list-suppression")
        await page.click("#label_svg_0 .btn-svg-list-suppression")
        await page.click("#label_svg_0 .btn-svg-list-suppression")
        await page.click("#label_svg_0 .btn-svg-list-suppression")
        expect(await page.evaluate(() => { return document.querySelector("#label_svg_0 img") })).toBeNull();
    })
    it("Téléchargement du pdf signé", async () => {
        await page._client.send('Page.setDownloadBehavior', {behavior: 'allow', downloadPath: './tests/downloads'});
        await page.click("#save");
        await page.waitForTimeout(600);
        await expect(require('fs').existsSync('./tests/downloads/document_signe.pdf')).toBe(true);
    });
    afterAll(async () => {
        if(process.env.DEBUG) {
            return;
        }
        await require('fs').unlinkSync('./tests/downloads/document_signe.pdf');
        await server.kill();
        await browser.close();
    });
});
