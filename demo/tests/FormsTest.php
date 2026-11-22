<?php

declare(strict_types=1);

namespace App\Tests;

use Playwright\Locator\LocatorInterface;
use Playwright\Page\PageInterface;

use function Playwright\Testing\expect;

final class FormsTest extends DemoTestCase
{
    public function testFieldTracksItsStateAndValidatesOnSubmit(): void
    {
        $page = $this->visitComponent('field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $input = $hero->getByRole('textbox', ['name' => 'Name']);
        $error = $hero->getByText('Please enter your name');

        // The parts link with the ids of the Field, as in Base UI.
        expect($hero->locator('label', ['hasText' => 'Name']))->toHaveAttribute('for', 'name-control');
        expect($input)->toHaveAttribute('id', 'name-control');
        expect($input)->toHaveAttribute('aria-describedby', 'name-description');

        $input->focus();
        expect($input)->toHaveAttribute('data-focused', '');

        // The validation mode is onSubmit, as in Base UI: a blur shows no error.
        $input->blur();
        expect($input)->toHaveAttribute('data-touched', '');
        expect($error)->toBeHidden();

        $input->fill('Colm');
        expect($input)->toHaveAttribute('data-dirty', '');
        expect($input)->toHaveAttribute('data-filled', '');
    }

    public function testFieldShowsItsErrorAfterAValidation(): void
    {
        $page = $this->visitComponent('field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $input = $hero->getByRole('textbox', ['name' => 'Name']);
        $error = $hero->getByText('Please enter your name');

        // Enter validates the field: the error shows, and the input names it.
        $input->focus();
        $page->keyboard()->press('Enter');
        expect($error)->toBeVisible();
        expect($input)->toHaveAttribute('aria-invalid', 'true');
        expect($input)->toHaveAttribute('data-invalid', '');
        $id = $error->evaluate('el => el.id');
        self::assertNotSame('', $id);
        self::assertContains($id, explode(' ', (string) $input->getAttribute('aria-describedby')));

        // A valid value removes the error.
        $input->fill('Colm');
        expect($error)->toBeHidden();
        expect($input)->toHaveAttribute('data-valid', '');
    }

    public function testFormShowsTheErrorOfTheServerAndFocusesAnInvalidField(): void
    {
        $page = $this->visitComponent('form');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $input = $hero->getByRole('textbox', ['name' => 'Username']);
        $submit = $hero->getByRole('button', ['name' => 'Submit']);
        expect($input)->toHaveValue('admin');

        // The value of the demo is valid for the browser: the server refuses it after 1 second.
        $submit->click();
        expect($hero->locator('text=\'admin\' is reserved for system use'))->toBeVisible();
        expect($input)->toHaveAttribute('aria-invalid', 'true');
        expect($input)->toHaveAttribute('aria-describedby', 'username-error');
        // The lock of the submit (`aria-disabled`) goes at the end of the request.
        expect($submit)->not()->toHaveAttribute('aria-disabled', 'true');

        // An empty value does not go to the server: the field shows the error of the browser,
        // and the control gets the focus.
        $input->fill('');
        $submit->click();
        expect($input)->toBeFocused();
        expect($input)->toHaveAttribute('data-invalid', '');
        expect($hero->locator('text=\'admin\' is reserved for system use'))->toBeHidden();
    }

    public function testFormMapsItsValuesToAnObjectAndShowsTheErrorsOfTheValidator(): void
    {
        $page = $this->visitComponent('form');
        $form = $page->getByRole('region', ['name' => 'Validate with #[MapForm]'])->locator('[data-demo="preview"]');

        // The object is not valid: the handler does not run, and each error goes to its field.
        $form->getByRole('textbox', ['name' => 'Age'])->fill('-2');
        $form->getByRole('button', ['name' => 'Submit'])->click();
        expect($form->locator('text=Name is required'))->toBeVisible();
        expect($form->locator('text=Age must be a positive number'))->toBeVisible();
        expect($form->getByRole('textbox', ['name' => 'Age']))->toHaveAttribute('aria-describedby', 'age-error');
        expect($form->getByRole('status'))->toHaveCount(0);

        // A valid object runs the handler and removes the errors.
        $form->getByRole('textbox', ['name' => 'Name'])->fill('Colm');
        $form->getByRole('textbox', ['name' => 'Age'])->fill('30');
        $form->getByRole('button', ['name' => 'Submit'])->click();
        expect($form->getByRole('status'))->toHaveText('Saved Colm, 30');
        expect($form->locator('text=Name is required'))->toBeHidden();
        expect($form->getByRole('textbox', ['name' => 'Age']))->not()->toHaveAttribute('aria-invalid', 'true');
    }

    public function testNumberField(): void
    {
        $page = $this->visitComponent('number-field');

        // With a radius, the group is round on its outer corners only.
        $page->evaluate('() => document.documentElement.style.setProperty("--radius", "0.5rem")');
        $radius = 'el => [getComputedStyle(el).borderTopLeftRadius, getComputedStyle(el).borderTopRightRadius]';
        self::assertSame(['6.4px', '0px'], $page->getByRole('button', ['name' => 'Decrease'])->evaluate($radius));
        self::assertSame(['0px', '0px'], $page->getByRole('textbox', ['name' => 'Amount'])->evaluate($radius));
        self::assertSame(['0px', '6.4px'], $page->getByRole('button', ['name' => 'Increase'])->evaluate($radius));
        $page->evaluate('() => document.documentElement.style.removeProperty("--radius")');

        // With a control height, the group and its square buttons take it.
        $page->evaluate('() => document.documentElement.style.setProperty("--control-height", "2.25rem")');
        $size = 'el => [el.offsetWidth, el.offsetHeight]';
        self::assertSame([36, 36], $page->getByRole('button', ['name' => 'Decrease'])->evaluate($size));
        self::assertSame(36, $page->getByRole('textbox', ['name' => 'Amount'])->evaluate('el => el.offsetHeight'));
        $page->evaluate('() => document.documentElement.style.removeProperty("--control-height")');

        $input = $page->getByRole('textbox', ['name' => 'Amount']);
        expect($input)->toHaveValue('100');

        $page->getByRole('button', ['name' => 'Increase'])->click();
        expect($input)->toHaveValue('101');
        $page->getByRole('button', ['name' => 'Decrease'])->click();
        $page->getByRole('button', ['name' => 'Decrease'])->click();
        expect($input)->toHaveValue('99');

        // The arrow keys step the value, and Shift steps by 10.
        $input->focus();
        $page->keyboard()->press('ArrowUp');
        expect($input)->toHaveValue('100');
        $page->keyboard()->press('Shift+ArrowUp');
        expect($input)->toHaveValue('110');
    }

    public function testNumberFieldRepeatsTheStepWhileTheButtonIsHeld(): void
    {
        $page = $this->visitComponent('number-field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $input = $hero->getByRole('textbox', ['name' => 'Amount']);

        // One step, then a step each 60 ms after 400 ms.
        self::hold($page, $hero->getByRole('button', ['name' => 'Increase']), 1000);
        self::assertGreaterThan(110, (int) $input->inputValue());
    }

    public function testNumberFieldScrubAreaChangesTheValue(): void
    {
        $page = $this->visitComponent('number-field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $input = $hero->getByRole('textbox', ['name' => 'Amount']);

        // The movements of a locked pointer in a headless browser come with the moves back to the
        // center. With no pointer lock, the controller reads the position of the pointer.
        $page->evaluate('() => { Element.prototype.requestPointerLock = undefined }');

        $box = $hero->locator('[data-slot="number-field-scrub-area"]')->boundingBox();
        $mouse = $page->mouse();
        $mouse->move($box['x'] + 5, $box['y'] + 5);
        $mouse->down();
        expect($hero->locator('[data-slot="number-field-scrub-area"]'))->toHaveAttribute('data-scrubbing', '');
        $mouse->move($box['x'] + 45, $box['y'] + 5, ['steps' => 10]);
        $mouse->up();

        // 10 movements of 4 pixels: one step for each pixel, as in Base UI.
        expect($input)->toHaveValue('140');
    }

    public function testNumberFieldWheelAndLimits(): void
    {
        $page = $this->visitComponent('number-field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $input = $hero->getByRole('textbox', ['name' => 'Amount']);
        $root = $hero->locator('[data-slot="number-field"]');

        // The demo of Base UI has no `allowWheelScrub` and no limits: the values turn them on.
        $root->evaluate('el => { el.setAttribute("data-phem-number-field-allow-wheel-scrub-value", "true"); el.setAttribute("data-phem-number-field-max-value", "102") }');

        $input->focus();
        $box = $input->boundingBox();
        $page->mouse()->move($box['x'] + 5, $box['y'] + 5);
        $page->mouse()->wheel(0, -100);
        expect($input)->toHaveValue('101');

        // At the limit, the value stays and the button is disabled.
        $increase = $hero->getByRole('button', ['name' => 'Increase']);
        $increase->click();
        expect($input)->toHaveValue('102');
        expect($increase)->toBeDisabled();
    }

    public function testOtpFieldMovesToTheNextSlot(): void
    {
        $page = $this->visitComponent('otp-field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $first = $hero->getByRole('textbox', ['name' => 'Verification code']);
        $first->focus();
        // The default validation type is numeric: a letter does not fill a slot.
        $page->keyboard()->type('1a234');
        $slots = $hero->locator('input[data-phem-otp-field-target="input"]');
        expect($slots->nth(4))->toBeFocused();
        expect($slots->nth(3))->toHaveValue('4');

        // Backspace in the empty slot 4 goes back and removes the character of slot 3.
        $page->keyboard()->press('Backspace');
        expect($slots->nth(3))->toBeFocused();
        expect($slots->nth(3))->toHaveValue('');
        expect($hero->locator('input[type="hidden"][name="code"]'))->toHaveValue('123');

        $page->keyboard()->type('456');
        expect($hero->locator('input[type="hidden"][name="code"]'))->toHaveValue('123456');
        expect($hero->locator('[data-controller~="phem-otp-field"]'))->toHaveAttribute('data-complete', '');
    }

    public function testOtpFieldPasteFillsAllTheSlots(): void
    {
        $page = $this->visitComponent('otp-field');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');

        $hero->getByRole('textbox', ['name' => 'Verification code'])->click();
        // A paste event with the text (the PHP client can run the script two times).
        $page->evaluate("() => window.pasted || (() => { window.pasted = true; const data = new DataTransfer(); data.setData('text/plain', '123456'); document.activeElement.dispatchEvent(new ClipboardEvent('paste', { clipboardData: data, bubbles: true, cancelable: true })) })()");

        expect($hero->locator('[data-slot="otp-field"]'))->toHaveAttribute('data-complete', '');
        expect($hero->locator('[data-slot="otp-field-input"]')->last())->toHaveValue('6');
    }

    public function testOtpFieldExamples(): void
    {
        $page = $this->visitComponent('otp-field');

        // The label names the first slot, and the other slots get their own label.
        $labeled = $page->getByRole('region', ['name' => 'Labeling an OTP field'])->locator('[data-demo="preview"]');
        expect($labeled->getByRole('textbox', ['name' => 'Verification code']))->toHaveAttribute('id', 'labeled-1');
        expect($labeled->getByRole('textbox', ['name' => 'Character 2 of 6']))->toBeVisible();

        // In a Field, the label names the first slot, and the form gets the code.
        $form = $page->getByRole('region', ['name' => 'Form integration'])->locator('[data-demo="preview"]');
        $form->getByRole('textbox', ['name' => 'Verification code'])->focus();
        $page->keyboard()->type('123456');
        expect($form->locator('input[name="verificationCode"]'))->toHaveValue('123456');

        // Alphanumeric: letters and digits.
        $recovery = $page->getByRole('region', ['name' => 'Alphanumeric verification codes'])->locator('[data-demo="preview"]');
        $recovery->getByRole('textbox', ['name' => 'Recovery code'])->focus();
        $page->keyboard()->type('a7c');
        expect($recovery->getByRole('textbox', ['name' => 'Character 3 of 6']))->toHaveValue('c');

        // The separator of a grouped layout.
        expect($page->getByRole('region', ['name' => 'Grouped layouts'])->locator('[data-demo="preview"]')->getByRole('separator'))->toHaveAttribute('data-orientation', 'horizontal');

        // Custom normalization: the letters become capital letters, and a dash is refused.
        $normalized = $page->getByRole('region', ['name' => 'Custom normalization'])->locator('[data-demo="preview"]');
        $normalized->getByRole('textbox', ['name' => 'Recovery code'])->focus();
        $page->keyboard()->type('a-b');
        expect($normalized->getByRole('textbox', ['name' => 'Recovery code']))->toHaveValue('A');
        expect($normalized->getByRole('textbox', ['name' => 'Character 2 of 6']))->toHaveValue('B');

        // Masked entry: the slots are password inputs.
        expect($page->getByRole('region', ['name' => 'Masked entry'])->locator('[data-demo="preview"]')->locator('input[data-phem-otp-field-target="input"]')->first())->toHaveAttribute('type', 'password');
    }

    public function testSliderMovesWithTheKeyboard(): void
    {
        $page = $this->visitComponent('slider');

        $thumb = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]')->getByRole('slider', ['name' => 'Volume']);
        expect($thumb)->toHaveValue('25');

        $thumb->focus();
        $page->keyboard()->press('ArrowRight');
        expect($thumb)->toHaveValue('26');
        $page->keyboard()->press('End');
        expect($thumb)->toHaveValue('100');
        $page->keyboard()->press('Home');
        expect($thumb)->toHaveValue('0');
    }

    public function testSliderMovesWithAPointerDrag(): void
    {
        $page = $this->visitComponent('slider');
        $hero = $page->getByRole('region', ['name' => 'Demo'])->locator('[data-demo="preview"]');
        $thumb = $hero->getByRole('slider', ['name' => 'Volume']);
        $control = $hero->locator('[data-slot="slider-control"]')->boundingBox();
        $y = $control['y'] + $control['height'] / 2;
        $mouse = $page->mouse();

        // A press on the track moves the thumb to the point, and a drag moves it.
        $mouse->move($control['x'] + $control['width'] / 2, $y);
        $mouse->down();
        expect($hero->locator('[data-slot="slider-thumb"]'))->toHaveAttribute('data-dragging', '');
        expect($thumb)->toHaveValue('50');

        $mouse->move($control['x'] + $control['width'], $y, ['steps' => 5]);
        $mouse->up();
        expect($thumb)->toHaveValue('100');
        expect($hero->locator('[data-slot="slider-thumb"]'))->not()->toHaveAttribute('data-dragging', '');
    }

    public function testSliderExamples(): void
    {
        $page = $this->visitComponent('slider');

        // A range: each thumb has its own name and value.
        $range = $page->getByRole('region', ['name' => 'Range slider'])->locator('[data-demo="preview"]');
        expect($range->getByRole('slider', ['name' => 'Minimum value']))->toHaveValue('25');
        expect($range->getByRole('slider', ['name' => 'Maximum value']))->toHaveValue('45');

        // The steps: an arrow key adds the step, Shift with an arrow key the large step.
        $speed = $page->getByRole('region', ['name' => 'Steps'])->locator('[data-demo="preview"]')->getByRole('slider', ['name' => 'Playback speed']);
        $speed->focus();
        $page->keyboard()->press('ArrowRight');
        expect($speed)->toHaveValue('500');
        $page->keyboard()->press('Shift+ArrowRight');
        expect($speed)->toHaveValue('700');

        // The edge alignment keeps the thumb in the control at the max.
        $edge = $page->getByRole('region', ['name' => 'Thumb alignment'])->locator('[data-demo="preview"]');
        $edge->getByRole('slider', ['name' => 'Volume'])->focus();
        $page->keyboard()->press('End');
        $right = $edge->locator('[data-phem-slider-target="thumb"]')->evaluate('el => el.getBoundingClientRect().right');
        $control = $edge->locator('[data-phem-slider-target="control"]')->evaluate('el => el.getBoundingClientRect().right');
        self::assertLessThanOrEqual($control + 0.5, $right);

        // Vertical: ArrowUp raises the value.
        $vertical = $page->getByRole('region', ['name' => 'Vertical'])->locator('[data-demo="preview"]')->getByRole('slider', ['name' => 'Volume']);
        expect($vertical)->toHaveAttribute('aria-orientation', 'vertical');
        $vertical->focus();
        $page->keyboard()->press('ArrowUp');
        expect($vertical)->toHaveValue('36');

        // In a form, the inputs have the name of the slider.
        $form = $page->getByRole('region', ['name' => 'Form integration'])->locator('[data-demo="preview"]');
        expect($form->locator('input[name="volume"]'))->toHaveValue('50');
        expect($form->locator('input[name="price[]"]'))->toHaveCount(2);
    }

    /** Holds the mouse button on the element for the time. */
    private static function hold(PageInterface $page, LocatorInterface $element, int $ms): void
    {
        $box = $element->boundingBox();
        $mouse = $page->mouse();
        $mouse->move($box['x'] + $box['width'] / 2, $box['y'] + $box['height'] / 2);
        $mouse->down();
        $page->evaluate("() => new Promise((resolve) => setTimeout(resolve, {$ms}))");
        $mouse->up();
    }
}
